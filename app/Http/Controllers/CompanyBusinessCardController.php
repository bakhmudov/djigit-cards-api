<?php

namespace App\Http\Controllers;

use App\Models\CompanyBusinessCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyBusinessCardController extends Controller
{
    /**
     * Store a new created company business card.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
// Валидация только одного поля "name"
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

// Создание визитки компании с заполнением только поля "name"
        $company = CompanyBusinessCard::create($data);

        return response()->json(['data' => $company], 201);
    }

    /**
     * Update the specified company business card.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
// Валидация всех полей, которые могут быть обновлены
        $data = $request->validate([
            'logo' => 'nullable|string',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'addresses' => 'nullable|array',
            'addresses.*.type' => 'required|string',
            'addresses.*.address' => 'required|string',
            'websites' => 'nullable|array',
            'websites.*.type' => 'required|string',
            'websites.*.url' => 'required|string',
        ]);

// Обновление визитки компании
        $company = CompanyBusinessCard::findOrFail($id);
        $company->update($data);

// Обновление адресов
        $company->addresses()->delete();
        if (isset($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                $company->addresses()->create($address);
            }
        }

// Обновление веб-сайтов
        $company->websites()->delete();
        if (isset($data['websites'])) {
            foreach ($data['websites'] as $website) {
                $company->websites()->create($website);
            }
        }

        return response()->json(['data' => $company], 200);
    }

    /**
     * Show the specified company business card.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
// Получение визитки компании с адресами, веб-сайтами и сотрудниками
        $company = CompanyBusinessCard::with(['addresses', 'websites', 'employees'])->findOrFail($id);

        return response()->json(['data' => $company], 200);
    }

    /**
     * Remove the specified company business card.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $company = CompanyBusinessCard::findOrFail($id);
        $company->delete();

        return response()->json(['data' => 'Company business card deleted successfully'], 200);
    }
}
