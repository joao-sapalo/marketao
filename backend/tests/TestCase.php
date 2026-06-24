<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected static bool $dbCreated = false;

    protected function setUp(): void
    {
        if (!static::$dbCreated) {
            $this->createTestDatabase();
            static::$dbCreated = true;
        }

        parent::setUp();
    }

    private function createTestDatabase(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dsn = config('database.connections.pgsql.database');
            config(['database.connections.pgsql.database' => 'postgres']);

            DB::statement("CREATE DATABASE \"{$dsn}\"");
            DB::statement("GRANT ALL PRIVILEGES ON DATABASE \"{$dsn}\" TO " . config('database.connections.pgsql.username'));

            config(['database.connections.pgsql.database' => $dsn]);
        }
    }
}
