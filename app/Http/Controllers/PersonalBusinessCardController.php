<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCardRequest;
use App\Models\PersonalBusinessCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PersonalBusinessCardController extends Controller
{
    /**
     * Store a new personal business card.
     *
     * @param CreateCardRequest $request
     * @return JsonResponse
     */
    public function store(CreateCardRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $card = PersonalBusinessCard::create($data);

        return response()->json(['data' => ['status' => 'Card created successfully', 'card' => $card]], 201);
    }

    /**
     * Update the specified personal business card.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        Log::info('Update method called', ['user_id' => Auth::id(), 'card_id' => $id]);

        $card = PersonalBusinessCard::findOrFail($id);

        if ($card->user_id !== Auth::id()) {
            Log::warning('Unauthorized update attempt', ['user_id' => Auth::id(), 'card_id' => $id]);
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->all();
        Log::info('Data received', ['data' => $data]);

        // Handle photo removal
        if ($request->input('remove_photo')) {
            if ($card->photo) {
                Storage::delete(str_replace('/storage/', 'public/', $card->photo));
                Log::info('Photo removed successfully', ['card_id' => $card->id]);
                $card->photo = null;
            }
        }

        // Handle photo upload
        $image_path = null;
        if ($request->hasFile('photo')) {
            Log::info('Photo detected in request');
            try {
                $image_path = $request->file('photo')->store('public/photos');
                Log::info('Photo uploaded successfully', ['image_path' => $image_path]);

                // Remove old photo if exists
                if ($card->photo) {
                    Storage::delete(str_replace('/storage/', 'public/', $card->photo));
                }

                // Update photo path in data
                $data['photo'] = str_replace('public/', '/storage/', $image_path);
            } catch (\Exception $e) {
                Log::error('Photo upload failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Photo upload failed'], 500);
            }
        }

        try {
            $validatedData = $this->validateCardData($data);
            $this->updateCardData($card, $validatedData);
            $this->updateRelatedData($card, $data);

            $card->refresh();

            return response()->json([
                'data' => [
                    'status' => 'Card updated successfully',
                    'card' => $this->formatCardResponse($card)
                ],
                'image' => $data['photo'] ?? null
            ], 200);
        } catch (\Exception $e) {
            Log::error('Card update failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Card update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validate card data.
     *
     * @param array $data
     * @return array
     */
    private function validateCardData(array $data): array
    {
        return Validator::make($data, [
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
    }

    /**
     * Display the specified personal business card.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $card = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])->findOrFail($id);
        return response()->json(['data' => $this->formatCardResponse($card)]);
    }

    /**
     * Display a listing of the personal business cards.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $cards = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])
            ->where('user_id', Auth::id())
            ->get()
            ->map([$this, 'formatCardResponse']);

        return response()->json(['data' => $cards], 200);
    }

    /**
     * Remove the specified personal business card.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $card = PersonalBusinessCard::findOrFail($id);

        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $card->phones()->delete();
        $card->emails()->delete();
        $card->addresses()->delete();
        $card->websites()->delete();
        $card->delete();

        return response()->json(['data' => ['status' => 'Card deleted successfully']], 200);
    }

    /**
     * Validate card data.
     *
     * @param array $data
     * @return array
     */
    private function validateCardData(array $data): array
    {
        return Validator::make($data, [
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
    }

    /**
     * Update card data.
     *
     * @param PersonalBusinessCard $card
     * @param array $validatedData
     */
    private function updateCardData(PersonalBusinessCard $card, array $validatedData): void
    {
        foreach ($validatedData as $key => $value) {
            if ($value !== null) {
                $card->{$key} = $value;
            }
        }
        $card->save();
    }

    /**
     * Update related data for the card.
     *
     * @param PersonalBusinessCard $card
     * @param array $data
     */
    private function updateRelatedData(PersonalBusinessCard $card, array $data): void
    {
        $this->updateRelationType($card, $data['phones'] ?? null, 'phones', 'number');
        $this->updateRelationType($card, $data['emails'] ?? null, 'emails', 'email');
        $this->updateRelationType($card, $data['addresses'] ?? null, 'addresses', 'address');
        $this->updateRelatedSocial($card, $data['websites'] ?? null, 'websites');
    }

    /**
     * Update related data of a specific type.
     *
     * @param PersonalBusinessCard $card
     * @param array|null $relatedData
     * @param string $relation
     * @param string $field
     */
    private function updateRelationType(PersonalBusinessCard $card, ?array $relatedData, string $relation, string $field): void
    {
        if ($relatedData) {
            $card->$relation()->delete();
            foreach ($relatedData as $type => $value) {
                if (!empty($value)) {
                    $value = is_array($value) ? implode(', ', $value) : $value;
                    $card->$relation()->create([
                        'type' => $type,
                        $field => $value,
                        'business_card_id' => $card->id,
                    ]);
                }
            }
        }
    }

    /**
     * Update related social data.
     *
     * @param PersonalBusinessCard $card
     * @param array|null $relatedData
     * @param string $relation
     */
    private function updateRelatedSocial(PersonalBusinessCard $card, ?array $relatedData, string $relation): void
    {
        if ($relatedData) {
            $card->$relation()->delete();
            $card->$relation()->create(array_merge($relatedData, ['business_card_id' => $card->id]));
        }
    }

    /**
     * Format card response.
     *
     * @param PersonalBusinessCard $card
     * @return array
     */
    public function formatCardResponse(PersonalBusinessCard $card): array
    {
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
            'phones' => $this->formatRelatedData($card->phones, 'number'),
            'emails' => $this->formatRelatedData($card->emails, 'email'),
            'addresses' => $this->formatRelatedData($card->addresses, 'address'),
            'websites' => $card->websites->first() ? [
                'site' => $card->websites->first()->site,
                'instagram' => $card->websites->first()->instagram,
                'telegram' => $card->websites->first()->telegram,
                'vk' => $card->websites->first()->vk,
            ] : null,
        ];
    }

    /**
     * Format related data.
     *
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @param string $field
     * @return array
     */
    private function formatRelatedData($data, string $field): array
    {
        $formatted = [];
        foreach ($data as $item) {
            if ($item->type === 'other') {
                $formatted['other'][] = $item->$field;
            } else {
                $formatted[$item->type] = $item->$field;
            }
        }
        return $formatted;
    }
}