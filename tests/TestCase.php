<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $database = (string) config('database.connections.mysql.database');

        if (! str_ends_with($database, '_test') && ! str_ends_with($database, '_testing')) {
            throw new RuntimeException("La suite requiere una base MySQL aislada; se recibió [{$database}].");
        }
    }
}
