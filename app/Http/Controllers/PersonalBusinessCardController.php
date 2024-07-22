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
     * @param Request $request
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
     *
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
            'emails.main' => 'nullable|string|max:25',
            'emails.work' => 'nullable|string|max:25',
            'emails.home' => 'nullable|string|max:25',
            'emails.other' => 'nullable|array',
            'addresses.main' => 'nullable|string|max:25',
            'addresses.work' => 'nullable|string|max:25',
            'addresses.home' => 'nullable|string|max:25',
            'addresses.other' => 'nullable|array',
            'websites.main' => 'nullable|string|max:255',
            'website.other' => 'nullable|array',
        ]);

        $card = PersonalBusinessCard::findOrFail($id);
        $card->update($request->only('fio', 'company_name', 'job_position', 'photo', 'main_info'));

        // Обновление телефонов
        if ($request->has('phones')) {
            $card->phones()->delete(); // Удаление старых записей
            foreach ($request->phones as $type => $number) {
                if (is_array($number)) {
                    foreach ($number as $num) {
                        $card->phones()->create(['type' => $type, 'number' => $num]);
                    }
                } else {
                    $card->phones()->create(['type' => $type, 'number' => $number]);
                }
            }
        }

        // Обновление email
        if ($request->has('emails')) {
            $card->emails()->delete();
            foreach ($request->emails as $type => $email) {
                if (is_array($email)) {
                    foreach ($email as $em) {
                        $card->emails()->create(['type' => $type, 'email' => $em]);
                    }
                } else {
                    $card->emails()->create(['type' => $type, 'email' => $email]);
                }
            }
        }

        // Обновление адресов
        if ($request->has('addresses')) {
            $card->addresses()->delete();
            foreach ($request->addresses as $type => $address) {
                if (is_array($address)) {
                    foreach ($address as $addr) {
                        $card->emails()->create(['type' => $type, 'email' => $addr]);
                    }
                } else {
                    $card->emails()->create(['type' => $type, 'email' => $address]);
                }
            }
        }

        // Обновление веб-сайтов
        if ($request->has('websites')) {
            $card->websites()->delete();
            foreach ($request->websites as $type => $url) {
                if (is_array($url)) {
                    foreach ($url as $u) {
                        $card->emails()->create(['type' => $type, 'email' => $u]);
                    }
                } else {
                    $card->emails()->create(['type' => $type, 'email' => $url]);
                }
            }
        }

        return response()->json(['data' => ['status' => 'Card updated successfully']], 200);
    }
}
