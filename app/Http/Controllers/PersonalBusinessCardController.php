<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCardRequest;
use App\Models\PersonalBusinessCard;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'fio' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'job_position' => 'nullable|string|max:255',
        ]);

        $card = PersonalBusinessCard::create([
            'user_id' => Auth::id(),
            'fio' => $request->fio,
            'company_name' => $request->company_name,
            'job_position' => $request->job_position,
        ]);

        return response()->json(['data' => ['status' => 'Card created successfully']], 201);
    }

    /**
     * Update personal business card.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'photo' => 'nullable|string',
            'fio' => 'required|string|max:255',
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
        ]);

        $card = PersonalBusinessCard::findOrFail($id);
        $card->update($request->only('fio', 'company_name', 'job_position', 'photo', 'main_info'));

        // Обновление телефонов
        if ($request->has('phones')) {
            $card->phones()->delete(); // Удаление старых записей
            foreach ($request->phones as $type => $number) {
                if (is_array($number)) {
                    foreach ($number as $num) {
                        if (!empty($num)) {
                            $card->phones()->create(['type' => $type, 'number' => $num]);
                        }
                    }
                } else {
                    if (!empty($number)) {
                        $card->phones()->create(['type' => $type, 'number' => $number]);
                    }
                }
            }
        }

        // Обновление email
        if ($request->has('emails')) {
            $card->emails()->delete();
            foreach ($request->emails as $type => $email) {
                if (is_array($email)) {
                    foreach ($email as $em) {
                        if (!empty($em)) {
                            $card->emails()->create(['type' => $type, 'email' => $em]);
                        }
                    }
                } else {
                    if (!empty($email)) {
                        $card->emails()->create(['type' => $type, 'email' => $email]);
                    }
                }
            }
        }

        // Обновление адресов
        if ($request->has('addresses')) {
            $card->addresses()->delete();
            foreach ($request->addresses as $type => $address) {
                if (is_array($address)) {
                    foreach ($address as $addr) {
                        if (!empty($addr)) {
                            $card->addresses()->create(['type' => $type, 'address' => $addr]);
                        }
                    }
                } else {
                    if (!empty($address)) {
                        $card->addresses()->create(['type' => $type, 'address' => $address]);
                    }
                }
            }
        }

        // Обновление веб-сайтов
        if ($request->has('websites')) {
            $card->websites()->delete();
            foreach ($request->websites as $type => $url) {
                if (is_array($url)) {
                    foreach ($url as $u) {
                        if (!empty($u)) {
                            $card->websites()->create(['type' => $type, 'url' => $u]);
                        }
                    }
                } else {
                    if (!empty($url)) {
                        $card->websites()->create(['type' => $type, 'url' => $url]);
                    }
                }
            }
        }

        return response()->json(['data' => ['status' => 'Card updated successfully']], 200);
    }


    public function show($id): JsonResponse
    {
        $card = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])->findOrFail($id);

        $response = [
            'id' => $card->id,
            'photo' => $card->photo,
            'fio' => $card->fio,
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
                'main' => $card->emails->where('type', 'main')->pluck('number')->first(),
                'work' => $card->emails->where('type', 'work')->pluck('number')->first(),
                'home' => $card->emails->where('type', 'home')->pluck('number')->first(),
                'other' => $card->emails->where('type', 'other')->pluck('number')->toArray(),
            ],
            'addresses' => [
                'main' => $card->addresses->where('type', 'main')->pluck('number')->first(),
                'work' => $card->addresses->where('type', 'work')->pluck('number')->first(),
                'home' => $card->addresses->where('type', 'home')->pluck('number')->first(),
                'other' => $card->addresses->where('type', 'other')->pluck('number')->toArray(),
            ],
            'websites' => [
                'main' => $card->websites->where('type', 'main')->pluck('url')->first(),
                'other' => $card->websites->where('type', 'other')->pluck('url')->toArray(),
            ],
        ];

        return response()->json(['data' => $response], 200);
    }
}
