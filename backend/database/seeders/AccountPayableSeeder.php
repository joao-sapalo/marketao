<?php

namespace Database\Seeders;

use App\Models\AccountPayable;
use App\Models\Purchase;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AccountPayableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $purchases = Purchase::all();

        if ($suppliers->isEmpty() || $purchases->isEmpty()) {
            return;
        }

        $payables = [
            ['supplier' => 0, 'amount' => 35000, 'due_date' => Carbon::now()->subDays(10), 'status' => 'overdue'],
            ['supplier' => 1, 'amount' => 22000, 'due_date' => Carbon::now()->addDays(15), 'status' => 'pending'],
            ['supplier' => 2, 'amount' => 18000, 'due_date' => Carbon::now()->subDays(5), 'status' => 'paid', 'paid_at' => Carbon::now()->subDays(4)],
            ['supplier' => 3, 'amount' => 45000, 'due_date' => Carbon::now()->addDays(30), 'status' => 'pending'],
            ['supplier' => 4, 'amount' => 12000, 'due_date' => Carbon::now()->subDays(20), 'status' => 'overdue'],
        ];

        foreach ($payables as $data) {
            $supplier = $suppliers->skip($data['supplier'])->first() ?? $suppliers->first();
            $purchase = $purchases->random();

            $record = AccountPayable::create([
                'supplier_id' => $supplier->id,
                'purchase_id' => $purchase->id,
                'amount' => $data['amount'],
                'due_date' => $data['due_date']->format('Y-m-d'),
                'status' => $data['status'],
                'paid_at' => $data['paid_at'] ?? null,
            ]);

            $record->created_at = $data['due_date'];
            $record->updated_at = $data['paid_at'] ?? $data['due_date'];
            $record->save();
        }
    }
}
