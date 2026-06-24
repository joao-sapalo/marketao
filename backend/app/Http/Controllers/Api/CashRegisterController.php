<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashRegisterRequest;
use App\Http\Resources\CashRegisterResource;
use App\Models\CashRegister;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CashRegisterController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CashRegisterResource::collection(CashRegister::latest()->paginate(15));
    }

    public function store(CashRegisterRequest $request): CashRegisterResource
    {
        $cashRegister = CashRegister::create([
            'user_id' => $request->user()->id,
            'opening_balance' => $request->opening_balance,
            'notes' => $request->notes,
            'opened_at' => Carbon::now(),
            'status' => 'open',
        ]);

        return new CashRegisterResource($cashRegister);
    }

    public function show(CashRegister $cashRegister): CashRegisterResource
    {
        return new CashRegisterResource($cashRegister->load('movements'));
    }

    public function close(Request $request, CashRegister $cashRegister): JsonResponse
    {
        $totalMovements = $cashRegister->movements()
            ->selectRaw("SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) as total_in")
            ->selectRaw("SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as total_out")
            ->first();

        $closingBalance = $cashRegister->opening_balance
            + ($totalMovements->total_in ?? 0)
            - ($totalMovements->total_out ?? 0);

        $cashRegister->update([
            'closing_balance' => $closingBalance,
            'closed_at' => Carbon::now(),
            'status' => 'closed',
        ]);

        return response()->json(new CashRegisterResource($cashRegister));
    }
}
