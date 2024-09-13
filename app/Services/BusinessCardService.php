<?php

namespace App\Services;

use App\Models\PersonalBusinessCard;
use Illuminate\Support\Facades\Storage;

class BusinessCardService
{
    public function createCard(array $data): PersonalBusinessCard
    {
        return PersonalBusinessCard::create([
            'user_id' => $data['user_id'],
            'fio' => $data['fio'],
            'company_name' => $data['company_name'] ?? null,
            'job_position' => $data['job_position'] ?? null,
        ]);
    }

    public function updateCard(PersonalBusinessCard $card, array $data, $request)
    {
        if ($request->input('remove_photo') && $card->photo) {
            Storage::delete(str_replace('/storage/', 'public/', $card->photo));
            $card->photo = null;
        }

        if ($request->hasFile('photo')) {
            $image_path = $request->file('photo')->store('public/photos');
            $data['photo'] = str_replace('public/', '/storage/', $image_path);
        }

        $card->update($data);
        $this->updateRelatedData($card, $data);
    }

    public function deleteCard(PersonalBusinessCard $card)
    {
        $card->phones()->delete();
        $card->emails()->delete();
        $card->addresses()->delete();
        $card->websites()->delete();
        $card->delete();
    }

    public function formatCardResponse(PersonalBusinessCard $card): array
    {
        return [
            'id'=> $card->id,
            'photo' => $card->phones ? url($card->photo) : null,
            'fio' => $card->fio,
            'about_me' => $card->about_me,
            'company_name' => $card->company_name,
            'job_position' => $card->job_position,
            'main_info' => $card->main_info,
            'phones' => $this->formatRelatedData($card->phones, 'number'),
            'emails' => $this->formatRelatedData($card->phones, 'email'),
            'addresses' => $this->formatRelatedData($card->phones, 'address'),
            'websites' => [
                'site' => $card->websites->first()->site ?? null,
                'instagram' => $card->websites->first()->instagram ?? null,
                'telegram' => $card->websites->first()->telegram ?? null,
                'vk' => $card->websites->first()->vk ?? null,
            ],
        ];
    }

    protected function updateRelatedData(PersonalBusinessCard $card, array $data)
    {
        $this->updateRelation($card, $data['phones'] ?? [], 'phones', 'number');
        $this->updateRelation($card, $data['emails'] ?? [], 'emails', 'email');
        $this->updateRelation($card, $data['addresses'] ?? [], 'addresses', 'address');
        $this->updateRelatedSocial($card, $data['websites'] ?? []);
    }

    protected function updateRelation($card, $relatedData, $relation, $field)
    {
        if ($relatedData) {
            $card->relation()->delete();
            foreach ($relatedData as $type => $value) {
                if (!empty($value)) {
                    $value = is_array($value) ? implode(',', $value) : $value;
                    $card->relation()->create([
                        'type' => $type,
                        $field => $value,
                    ]);
                }
            }
        }
    }

    protected function updateRelatedSocial($card, $relatedData)
    {
        if ($relatedData) {
            $card->websites()->delete();
            $card->websites()->create($relatedData);
        }
    }

    protected function formatRelatedData($relatedData, $field)
    {
        $formatted = [
            'main' => $relatedData->where('type', 'main')->pluck($field)->first(),
            'work' => $relatedData->where('type', 'work')->pluck($field)->first(),
            'home' => $relatedData->where('type', 'home')->pluck($field)->first(),
            'other' => $relatedData->where('type', 'other')->pluck($field)->implode(', '),
        ];

        return array_filter($formatted);
    }
}