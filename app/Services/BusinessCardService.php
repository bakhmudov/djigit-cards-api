<?php

namespace App\Services;

use App\Models\PersonalBusinessCard;
use Illuminate\Support\Facades\Log;
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

    public function updateCard(PersonalBusinessCard $card, array $data)
    {
        Log::info('Updating card', ['card_id' => $card->id, 'data' => $data]);

        if (isset($data['remove_photo']) && $data['remove_photo'] && $card->photo) {
            Storage::delete(str_replace('/storage/', 'public/', $card->photo));
            $card->photo = null;
            Log::info('Photo removed', ['card_id' => $card->id]);
        }

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $image_path = $data['photo']->store('public/photos');
            $data['photo'] = str_replace('public/', '/storage/', $image_path);
            Log::info('New photo uploaded', ['card_id' => $card->id, 'path' => $data['photo']]);
        }

        $card->update($data);
        Log::info('Card updated', ['card_id' => $card->id]);

        $this->updateRelatedData($card, $data);
    }

    protected function updateRelatedData(PersonalBusinessCard $card, array $data)
    {
        $relations = ['phones', 'emails', 'addresses', 'websites'];

        foreach ($relations as $relation) {
            if (isset($data[$relation])) {
                $this->updateRelation($card, $data[$relation], $relation);
            }
        }
    }

    protected function updateRelation(PersonalBusinessCard $card, $relatedData, $relation)
    {
        if (!method_exists($card, $relation)) {
            Log::warning("Relation method {$relation} does not exist on PersonalBusinessCard model");
            return;
        }

        $card->$relation()->delete();

        Log::info("Updating relation: {$relation}", ['data' => $relatedData]);

        if ($relation === 'websites') {
            $card->websites()->create($relatedData);
        } else {
            foreach ($relatedData as $type => $values) {
                if (!empty($values)) {
                    $values = is_array($values) ? $values : [$values];
                    foreach ($values as $value) {
                        $card->$relation()->create([
                            'type' => $type,
                            $this->getFieldNameForRelation($relation) => $value
                        ]);
                    }
                }
            }
        }
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
            'id' => $card->id,
            'photo' => $card->photo ? url($card->photo) : null,
            'fio' => $card->fio,
            'about_me' => $card->about_me,
            'company_name' => $card->company_name,
            'job_position' => $card->job_position,
            'main_info' => $card->main_info,
            'phones' => $this->formatRelatedData($card->phones, 'number'),
            'emails' => $this->formatRelatedData($card->emails, 'email'),
            'addresses' => $this->formatRelatedData($card->addresses, 'address'),
            'websites' => [
                'site' => $card->websites->first()->site ?? null,
                'instagram' => $card->websites->first()->instagram ?? null,
                'telegram' => $card->websites->first()->telegram ?? null,
                'vk' => $card->websites->first()->vk ?? null,
            ],
        ];
    }

    private function getFieldNameForRelation($relation)
    {
        switch ($relation) {
            case 'phones':
                return 'number';
            case 'emails':
                return 'email';
            case 'addresses':
                return 'address';
            default:
                return 'value';
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