<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('whatsapp')->nullable()->after('phone');
            $table->boolean('accepts_cash')->default(true)->after('is_active');
            $table->boolean('accepts_transfer')->default(true)->after('accepts_cash');
            $table->boolean('accepts_multicaixa')->default(false)->after('accepts_transfer');
            $table->string('bank_name')->nullable()->after('accepts_multicaixa');
            $table->string('bank_holder')->nullable()->after('bank_name');
            $table->string('bank_iban')->nullable()->after('bank_holder');
            $table->decimal('trust_score', 5, 2)->default(0.00)->after('bank_iban');
            $table->integer('total_orders')->default(0)->after('trust_score');
            $table->integer('confirmed_orders')->default(0)->after('total_orders');
            $table->decimal('avg_delivery_days', 5, 2)->default(0.00)->after('confirmed_orders');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp',
                'accepts_cash',
                'accepts_transfer',
                'accepts_multicaixa',
                'bank_name',
                'bank_holder',
                'bank_iban',
                'trust_score',
                'total_orders',
                'confirmed_orders',
                'avg_delivery_days',
            ]);
        });
    }
};
