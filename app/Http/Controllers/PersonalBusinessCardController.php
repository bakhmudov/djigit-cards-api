<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\PersonalBusinessCard;
use App\Services\BusinessCardService;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonalBusinessCardController extends Controller
{
    protected $businessCardService;

    public function __construct(BusinessCardService $businessCardService)
    {
        $this->businessCardService = $businessCardService;
    }

    public function store(CreateCardRequest $request): JsonResponse
    {


        $data = $request->validated();
        $data['user_id'] = Auth::id();

        try {
            $card = $this->businessCardService->createCard($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Card created successfully',
                'data' => $card
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create card', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateCardRequest $request, $id): JsonResponse
    {

        // Поиск визитки по ID
        $card = PersonalBusinessCard::findOrFail($id);

        // Проверка, является ли текущий пользователь владельцем визитки
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validated();

        DB::transaction(function () use ($card, $data, $request) {
            $this->businessCardService->updateCard($card, $data, $request);
        });

        return response()->json(['data' => ['status' => 'Card updated successfully']], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $card = PersonalBusinessCard::findOrFail($id);

        // Проверка, является ли текущий пользователь владельцем визитки
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::transaction(function () use ($card) {
            $this->businessCardService->deleteCard($card);
        });

        return response()->json(['data' => ['status' => 'Card deleted successfully']], 200);
    }

    public function show(string $id): JsonResponse
    {
        $card = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])->findOrFail($id);
        $response = $this->businessCardService->formatCardResponse($card);

        return response()->json(['data' => $response]);
    }

    public function index(): JsonResponse
    {
        $cards = PersonalBusinessCard::with(['phones', 'emails', 'addresses', 'websites'])
            ->where('user_id', Auth::id())
            ->get();

        $response = $cards->map(function ($card) {
            return $this->businessCardService->formatCardResponse($card);
        });

        return response()->json(['data' => $response], 200);
    }
}
