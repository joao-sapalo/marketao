<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE sales DROP CONSTRAINT IF EXISTS sales_status_check');
        DB::statement("ALTER TABLE sales ADD CONSTRAINT sales_status_check CHECK (status::text = ANY (ARRAY['completed'::character varying, 'cancelled'::character varying, 'draft'::character varying, 'pending'::character varying]::text[]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sales DROP CONSTRAINT IF EXISTS sales_status_check');
        DB::statement("ALTER TABLE sales ADD CONSTRAINT sales_status_check CHECK (status::text = ANY (ARRAY['completed'::character varying, 'cancelled'::character varying, 'draft'::character varying]::text[]))");
    }
};
