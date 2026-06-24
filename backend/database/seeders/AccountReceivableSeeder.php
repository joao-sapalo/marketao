<?php

namespace Database\Seeders;

use App\Models\AccountReceivable;
use App\Models\Customer;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AccountReceivableSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $sales = Sale::all();

        $receivables = [
            ['customer' => 0, 'amount' => 25000, 'due_date' => Carbon::now()->subDays(5), 'status' => 'pending'],
            ['customer' => 1, 'amount' => 18200, 'due_date' => Carbon::now()->subDays(15), 'status' => 'paid', 'paid_at' => Carbon::now()->subDays(10)],
            ['customer' => 2, 'amount' => 45000, 'due_date' => Carbon::now()->addDays(10), 'status' => 'pending'],
            ['customer' => 3, 'amount' => 9800, 'due_date' => Carbon::now()->subDays(25), 'status' => 'overdue'],
            ['customer' => 4, 'amount' => 32000, 'due_date' => Carbon::now()->subDays(3), 'status' => 'pending'],
            ['customer' => 5, 'amount' => 7500, 'due_date' => Carbon::now()->subDays(40), 'status' => 'overdue'],
            ['customer' => 6, 'amount' => 12000, 'due_date' => Carbon::now()->subDays(2), 'status' => 'paid', 'paid_at' => Carbon::now()->subDays(1)],
            ['customer' => 7, 'amount' => 27800, 'due_date' => Carbon::now()->addDays(20), 'status' => 'pending'],
        ];

        foreach ($receivables as $data) {
            $customer = $customers->skip($data['customer'])->first() ?? $customers->first();
            $sale = $sales->random();

            $record = AccountReceivable::create([
                'customer_id' => $customer->id,
                'sale_id' => $sale->id,
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
