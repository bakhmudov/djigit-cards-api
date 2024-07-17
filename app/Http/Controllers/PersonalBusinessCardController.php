<?php

namespace App\Http\Controllers;

use App\Models\PersonalBusinessCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalBusinessCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cards = PersonalBusinessCard::all();
        return response()->json($cards);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // Добавьте остальные необходимые поля и правила валидации
        ]);

        $card = new PersonalBusinessCard($request->all());
        $card->user_id = Auth::id();
        $card->save();

        return response()->json($card, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $card = PersonalBusinessCard::findOrFail($id);
        return response()->json($card);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $card = PersonalBusinessCard::findOrFail($id);

        // Проверка на владельца визитки
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $card->update($request->all());

        return response()->json($card);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $card = PersonalBusinessCard::findOrFail($id);

        // Проверка на владельца визитки
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $card->delete();

        return response()->json(null, 204);
    }
}
