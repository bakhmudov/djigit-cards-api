<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCardRequest;
use App\Models\PersonalBusinessCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PersonalBusinessCardController extends Controller
{
    /**
     * Store a new created personal business card.
     *
     * @param CreateCardRequest $request
     * @return JsonResponse
     */
    public function store(CreateCardRequest $request): JsonResponse
    {
        $data = $request->validate([
            'fio' => 'required|string|max:255',
            'about_me' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'job_position' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = Auth::id();

        $card = PersonalBusinessCard::create($data);

        return response()->json(['data' => ['status' => 'Card created successfully', 'card' => $card]], 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        \Log::info('Update method called', ['user_id' => Auth::id(), 'card_id' => $id]);

        // Поиск визитки по ID
        $card = PersonalBusinessCard::findOrFail($id);
        \Log::info('Card found', ['card_id' => $card->id]);

        // Проверка, является ли текущий пользователь владельцем визитки
        if ($card->user_id !== Auth::id()) {
            \Log::warning('Unauthorized update attempt', ['user_id' => Auth::id(), 'card_id' => $id]);
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->all();
        \Log::info('Data received', ['data' => $data]);

        // Обработка удаления фотографии профиля
        if ($request->input('remove_photo')) {
            if ($card->photo) {
                \Storage::delete(str_replace('/storage/', 'public/', $card->photo));
                \Log::info('Photo removed successfully', ['card_id' => $card->id]);
                $card->photo = null;
            }
        }

        // Проверка и сохранение файла изображения
        $image_path = null;
        if ($request->hasFile('photo')) {
            \Log::info('Photo detected in request');
            try {
                $image_path = $request->file('photo')->store('public/photos');
                \Log::info('Photo uploaded successfully', ['image_path' => $image_path]);

                // Преобразуем путь для хранения в базе данных
                $data['photo'] = str_replace('public/', '/storage/', $image_path);
            } catch (\Exception $e) {
                \Log::error('Photo upload failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Photo upload failed'], 500);
            }
        } else {
            \Log::info('No new photo uploaded in the request');
        }

        \Log::info('Starting validation process');

        try {
            // Валидация данных
            $validatedData = Validator::make($data, [
                'photo' => 'nullable|string',
                'fio' => 'required|string|max:255',
                'about_me' => 'nullable|string',
                'company_name' => 'nullable|string|max:255',
                'job_position' => 'nullable|string|max:255',
                'main_info.phone' => 'nullable|string|max:25',
                'main_info.telegram' => 'nullable|string|max:255',
                'main_info.whatsapp' => 'nullable|string|max:255',
                'main_info.instagram' => 'nullable|string|max:255',
            ])->validate();
            \Log::info('Validation passed successfully', ['validated_data' => $validatedData]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        }

        // Обновляем только те поля, которые были переданы и прошли валидацию
        foreach ($validatedData as $key => $value) {
            if ($value !== null) {
                $card->{$key} = $value;
            }
        }

        // Обновление данных визитки
        try {
            $card->save();
            \Log::info('Card updated successfully', ['card_id' => $card->id, 'validated_data' => $validatedData]);
        } catch (\Exception $e) {
            \Log::error('Card update failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Card update failed'], 500);
        }

        // Обновление связанных данных
        \Log::info('Updating related data (phones, emails, addresses, websites)');
        try {
            $this->updateRelatedData($card, $data['phones'] ?? null, 'phones', 'number');
            $this->updateRelatedData($card, $data['emails'] ?? null, 'emails', 'email');
            $this->updateRelatedData($card, $data['addresses'] ?? null, 'addresses', 'address');
            $this->updateRelatedSocial($card, $data['websites'] ?? null, 'websites');
            \Log::info('Related data updated successfully', ['card_id' => $card->id]);
        } catch (\Exception $e) {
            \Log::error('Related data update failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Related data update failed'], 500);
        }

        // Логирование данных перед отправкой ответа
        \Log::info('Preparing response data', [
            'data' => ['status' => 'Card updated successfully'],
            'image' => $validatedData['photo'] ?? null
        ]);

        return response()->json(['data' => ['status' => 'Card updated successfully'], 'image' => $validatedData['photo'] ?? null], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $card = PersonalBusinessCard::findOrFail($id);

        // Проверка, является ли текущий пользователь владельцем визитки
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Удаление связанных данных
        $card->phones()->delete();
        $card->emails()->delete();
        $card->addresses()->delete();
        $card->websites()->delete();

        // Удаление самой визитки
        $card->delete();

        return response()->json(['data' => ['status' => 'Card deleted successfully']], 200);
    }


    private function updateRelatedSocial($card, $relatedData, $relation)
    {
        \Log::info("Updating related data for relation: {$relation}", ['relatedData' => $relatedData]);

        if ($relatedData) {
            try {
                // Удаление старых записей
                $card->$relation()->delete();
                \Log::info("Old {$relation} records deleted successfully.");

                // Добавление новой записи для каждого типа
                $card->$relation()->create([
                    'site' => $relatedData['site'] ?? null,
                    'instagram' => $relatedData['instagram'] ?? null,
                    'telegram' => $relatedData['telegram'] ?? null,
                    'vk' => $relatedData['vk'] ?? null,
                    'business_card_id' => $card->id,
                ]);

                \Log::info("Added new {$relation} records", ['relatedData' => $relatedData]);
            } catch (\Exception $e) {
                \Log::error("Failed to update related data for relation: {$relation}", ['error' => $e->getMessage()]);
                throw $e;
            }
        } else {
            \Log::info("No related data provided for relation: {$relation}");
        }
    }


    /**
     * @param $card
     * @param $relatedData
     * @param $relation
     * @param $field
     * @return void
     */
    private function updateRelatedData($card, $relatedData, $relation, $field)
    {
        \Log::info("Updating related data for relation: {$relation}", ['relatedData' => $relatedData]);

        if ($relatedData) {
            try {
                // Удаление старых записей
                $card->$relation()->delete();
                \Log::info("Old {$relation} records deleted successfully.");

                // Если поле other передано как строка, сохраняем его
                foreach ($relatedData as $type => $value) {
                    if (!empty($value)) {
                        // Преобразуем массивы в строки, если нужно
                        if (is_array($value)) {
                            $value = implode(', ', $value); // Превращаем массив в строку
                        }

                        $card->$relation()->create([
                            'type' => $type,
                            $field => $value,
                            'business_card_id' => $card->id,
                        ]);
                        \Log::info("Added new {$relation} record", [$field => $value, 'type' => $type]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Failed to update related data for relation: {$relation}", ['error' => $e->getMessage()]);
                throw $e;
            }
        } else {
            \Log::info("No related data provided for relation: {$relation}");
        }
    }
    
    /**
     * Show the specified personal business card.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $card = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])->findOrFail($id);

        $response = [
            'id' => $card->id,
            'photo' => $card->photo ? url($card->photo) : null,
            'fio' => $card->fio,
            'about_me' => $card->about_me,
            'company_name' => $card->company_name,
            'job_position' => $card->job_position,
            'main_info' => $card->main_info,
            'phones' => [
                'main' => $card->phones->where('type', 'main')->pluck('number')->first(),
                'work' => $card->phones->where('type', 'work')->pluck('number')->first(),
                'home' => $card->phones->where('type', 'home')->pluck('number')->first(),
                'other' => $card->phones->where('type', 'other')->pluck('number')->toArray(),
            ],
            'emails' => [
                'main' => $card->emails->where('type', 'main')->pluck('email')->first(),
                'work' => $card->emails->where('type', 'work')->pluck('email')->first(),
                'home' => $card->emails->where('type', 'home')->pluck('email')->first(),
                'other' => $card->emails->where('type', 'other')->pluck('email')->toArray(),
            ],
            'addresses' => [
                'main' => $card->addresses->where('type', 'main')->pluck('address')->first(),
                'work' => $card->addresses->where('type', 'work')->pluck('address')->first(),
                'home' => $card->addresses->where('type', 'home')->pluck('address')->first(),
                'other' => $card->addresses->where('type', 'other')->pluck('address')->toArray(),
            ],
            'websites' => [
                'site' => $card->websites->first()->site ?? null,
                'instagram' => $card->websites->first()->instagram ?? null,
                'telegram' => $card->websites->first()->telegram ?? null,
                'vk' => $card->websites->first()->vk ?? null,
            ],
        ];

        return response()->json(['data' => $response]);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $cards = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])
            ->where('user_id', $user->id)
            ->get();

        $response = $cards->map(function ($card) {
            return [
                'id' => $card->id,
                'user_id' => $card->user_id,
                'fio' => $card->fio,
                'about_me' => $card->about_me,
                'company_name' => $card->company_name,
                'job_position' => $card->job_position,
                'photo' => $card->photo ? url($card->photo) : null,
                'main_info' => $card->main_info,
                'created_at' => $card->created_at,
                'updated_at' => $card->updated_at,
                'phones' => $card->phones->map(function ($phone) {
                    return [
                        'type' => $phone->type,
                        'number' => $phone->number,
                    ];
                }),
                'emails' => $card->emails->map(function ($email) {
                    return [
                        'type' => $email->type,
                        'email' => $email->email,
                    ];
                }),
                'addresses' => $card->addresses->map(function ($address) {
                    return [
                        'type' => $address->type,
                        'address' => $address->address,
                    ];
                }),
                'websites' => [
                    'site' => $card->websites->first()->site ?? null,
                    'instagram' => $card->websites->first()->instagram ?? null,
                    'telegram' => $card->websites->first()->telegram ?? null,
                    'vk' => $card->websites->first()->vk ?? null,
                ],
            ];
        });

        return response()->json(['data' => $response], 200);
    }
}
