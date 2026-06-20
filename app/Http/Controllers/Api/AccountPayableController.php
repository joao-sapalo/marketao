<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountPayableRequest;
use App\Http\Resources\AccountPayableResource;
use App\Models\AccountPayable;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountPayableController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = AccountPayable::with('supplier');

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }

        if ($request->get('start_date')) {
            $query->whereDate('due_date', '>=', $request->get('start_date'));
        }

        if ($request->get('end_date')) {
            $query->whereDate('due_date', '<=', $request->get('end_date'));
        }

        return AccountPayableResource::collection($query->latest()->paginate(15));
    }

    public function store(AccountPayableRequest $request): AccountPayableResource
    {
        $account = AccountPayable::create($request->validated());

        return new AccountPayableResource($account->load('supplier'));
    }

    public function show(AccountPayable $accountPayable): AccountPayableResource
    {
        return new AccountPayableResource($accountPayable->load('supplier'));
    }

    public function update(AccountPayableRequest $request, AccountPayable $accountPayable): AccountPayableResource
    {
        $accountPayable->update($request->validated());

        return new AccountPayableResource($accountPayable->load('supplier'));
    }

    public function markAsPaid(AccountPayable $accountPayable): JsonResponse
    {
        $accountPayable->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        return response()->json(new AccountPayableResource($accountPayable->load('supplier')));
    }
}
