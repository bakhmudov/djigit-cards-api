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

        return response()->json(['data' => ['status' => 'Card created successfully']], 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Определение, как пришли данные (JSON или Form-Data)
        $contentType = $request->header('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $data = $request->json()->all();
        } else {
            // Если данные пришли в формате Form-Data
            $data = $request->all();

            // Проверка и сохранение файла изображения
            if ($request->hasFile('photo')) {
                $uploaded_image = $request->file('photo')->store('public/uploads/');
            }
        }

        // Валидация данных
        $validatedData = Validator::make($data, [
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'fio' => 'required|string|max:255',
            'about_me' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'job_position' => 'nullable|string|max:255',
            'main_info.phone' => 'nullable|string|max:25',
            'main_info.telegram' => 'nullable|string|max:255',
            'main_info.whatsapp' => 'nullable|string|max:255',
            'main_info.instagram' => 'nullable|string|max:255',
            'phones.main' => 'nullable|string|max:25',
            'phones.work' => 'nullable|string|max:25',
            'phones.home' => 'nullable|string|max:25',
            'phones.other' => 'nullable|array',
            'emails.main' => 'nullable|string|max:255',
            'emails.work' => 'nullable|string|max:255',
            'emails.home' => 'nullable|string|max:255',
            'emails.other' => 'nullable|array',
            'addresses.main' => 'nullable|string|max:255',
            'addresses.work' => 'nullable|string|max:255',
            'addresses.home' => 'nullable|string|max:255',
            'addresses.other' => 'nullable|array',
            'websites.main' => 'nullable|string|max:255',
            'websites.other' => 'nullable|array',
        ])->validate();

        $card = PersonalBusinessCard::findOrFail($id);
        $card->update($validatedData);

        // Обновление связанных данных
        $this->updateRelatedData($card, $data['phones'] ?? null, 'phones', 'number');
        $this->updateRelatedData($card, $data['emails'] ?? null, 'emails', 'email');
        $this->updateRelatedData($card, $data['addresses'] ?? null, 'addresses', 'address');
        $this->updateRelatedData($card, $data['websites'] ?? null, 'websites', 'url');

        return response()->json(['data' => ['status' => 'Card updated successfully'], ["image" => $uploaded_image]]);
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
        if ($relatedData) {
            $card->$relation()->delete(); // Удаление старых записей
            foreach ($relatedData as $type => $value) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (!empty($item)) {
                            $card->$relation()->create([
                                'type' => $type,
                                $field => $item,
                                'business_card_id' => $card->id,
                            ]);
                        }
                    }
                } else {
                    if (!empty($value)) {
                        $card->$relation()->create([
                            'type' => $type,
                            $field => $value,
                            'business_card_id' => $card->id,
                        ]);
                    }
                }
            }
        }
    }



    /**
     * Show the specified personal business card.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $card = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])->findOrFail($id);

        $response = [
            'id' => $card->id,
            'photo' => $card->photo,
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
                'main' => $card->websites->where('type', 'main')->pluck('url')->first(),
                'other' => $card->websites->where('type', 'other')->pluck('url')->toArray(),
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
                'photo' => $card->photo,
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
                'websites' => $card->websites->map(function ($website) {
                    return [
                        'type' => $website->type,
                        'url' => $website->url,
                    ];
                }),
            ];
        });

        return response()->json(['data' => $response], 200);
    }
}
