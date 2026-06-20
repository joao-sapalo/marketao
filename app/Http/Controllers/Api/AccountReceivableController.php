<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountReceivableRequest;
use App\Http\Resources\AccountReceivableResource;
use App\Models\AccountReceivable;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountReceivableController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = AccountReceivable::with('customer');

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('customer_id')) {
            $query->where('customer_id', $request->get('customer_id'));
        }

        if ($request->get('start_date')) {
            $query->whereDate('due_date', '>=', $request->get('start_date'));
        }

        if ($request->get('end_date')) {
            $query->whereDate('due_date', '<=', $request->get('end_date'));
        }

        return AccountReceivableResource::collection($query->latest()->paginate(15));
    }

    public function store(AccountReceivableRequest $request): AccountReceivableResource
    {
        $account = AccountReceivable::create($request->validated());

        return new AccountReceivableResource($account->load('customer'));
    }

    public function show(AccountReceivable $accountReceivable): AccountReceivableResource
    {
        return new AccountReceivableResource($accountReceivable->load('customer'));
    }

    public function update(AccountReceivableRequest $request, AccountReceivable $accountReceivable): AccountReceivableResource
    {
        $accountReceivable->update($request->validated());

        return new AccountReceivableResource($accountReceivable->load('customer'));
    }

    public function markAsPaid(AccountReceivable $accountReceivable): JsonResponse
    {
        $accountReceivable->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        return response()->json(new AccountReceivableResource($accountReceivable->load('customer')));
    }
}
