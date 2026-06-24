<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashMovementRequest;
use App\Http\Resources\CashMovementResource;
use App\Models\CashMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CashMovementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CashMovement::with('cashRegister');

        if ($request->get('cash_register_id')) {
            $query->where('cash_register_id', $request->get('cash_register_id'));
        }

        return CashMovementResource::collection($query->latest()->paginate(15));
    }

    public function store(CashMovementRequest $request): CashMovementResource
    {
        $movement = CashMovement::create([
            'cash_register_id' => $request->cash_register_id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return new CashMovementResource($movement);
    }

    public function show(CashMovement $cashMovement): CashMovementResource
    {
        return new CashMovementResource($cashMovement);
    }

    public function update(CashMovementRequest $request, CashMovement $cashMovement): CashMovementResource
    {
        $cashMovement->update($request->validated());

        return new CashMovementResource($cashMovement);
    }

    public function destroy(CashMovement $cashMovement): JsonResponse
    {
        $cashMovement->delete();

        return response()->json(['message' => 'Cash movement deleted successfully']);
    }
}
