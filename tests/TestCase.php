<?php

namespace Tests;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        if ($app['config']->get('database.default') === 'sqlsrv'
            && $app['config']->get('database.connections.sqlsrv.database') !== 'suara_pergerakan_test') {
            throw new \RuntimeException('SQL Server tests require the dedicated suara_pergerakan_test database.');
        }

        return $app;
    }
}
