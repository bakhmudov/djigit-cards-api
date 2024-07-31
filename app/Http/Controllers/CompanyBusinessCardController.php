<?php

namespace App\Http\Controllers;

use App\Models\CompanyBusinessCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyBusinessCardController extends Controller
{
    public function store(Request $request): JsonResponse
    {
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

        $company = CompanyBusinessCard::create($data);

        if (isset($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                $company->addresses()->create($address);
            }
        }

        if (isset($data['websites'])) {
            foreach ($data['websites'] as $website) {
                $company->websites()->create($website);
            }
        }

        return response()->json(['data' => $company], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $company = CompanyBusinessCard::findOrFail($id);

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

        $company->update($data);

        $company->addresses()->delete();
        if (isset($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                $company->addresses()->create($address);
            }
        }

        $company->websites()->delete();
        if (isset($data['websites'])) {
            foreach ($data['websites'] as $website) {
                $company->websites()->create($website);
            }
        }

        return response()->json(['data' => $company], 200);
    }

    public function show($id): JsonResponse
    {
        $company = CompanyBusinessCard::with(['addresses', 'websites', 'employees'])->findOrFail($id);

        return response()->json(['data' => $company], 200);
    }

    public function destroy($id): JsonResponse
    {
        $company = CompanyBusinessCard::findOrFail($id);
        $company->delete();

        return response()->json(['data' => 'Company business card deleted successfully'], 200);
    }
}
