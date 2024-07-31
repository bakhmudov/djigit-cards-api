<?php

namespace App\Http\Controllers;

use App\Models\EmployeeBusinessCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeBusinessCardController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_business_card_id' => 'required|exists:company_business_cards,id',
            'photo' => 'nullable|string',
            'fio' => 'required|string|max:255',
            'job_position' => 'required|string|max:255',
            'main_info' => 'nullable|array',
            'phones' => 'nullable|array',
            'emails' => 'nullable|array',
            'addresses' => 'nullable|array',
            'websites' => 'nullable|array',
        ]);

        $employee = EmployeeBusinessCard::create($data);

        return response()->json(['data' => $employee], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $employee = EmployeeBusinessCard::findOrFail($id);

        $data = $request->validate([
            'photo' => 'nullable|string',
            'fio' => 'required|string|max:255',
            'job_position' => 'required|string|max:255',
            'main_info' => 'nullable|array',
            'phones' => 'nullable|array',
            'emails' => 'nullable|array',
            'addresses' => 'nullable|array',
            'websites' => 'nullable|array',
        ]);

        $employee->update($data);

        return response()->json(['data' => $employee], 200);
    }

    public function show($id): JsonResponse
    {
        $employee = EmployeeBusinessCard::findOrFail($id);

        return response()->json(['data' => $employee], 200);
    }

    public function destroy($id): JsonResponse
    {
        $employee = EmployeeBusinessCard::findOrFail($id);
        $employee->delete();

        return response()->json(['data' => 'Employee business card deleted successfully'], 200);
    }
}
