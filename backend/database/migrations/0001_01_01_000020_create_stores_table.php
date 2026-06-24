<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('primary_color')->default('#2563eb');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete()->after('supplier_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete()->after('id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->string('source')->default('pos')->after('status');
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->text('delivery_address')->nullable()->after('notes');
            $table->string('payment_method')->nullable()->after('delivery_address');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'source', 'customer_name', 'customer_phone', 'customer_email', 'delivery_address', 'payment_method']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
        Schema::dropIfExists('stores');
    }
};
