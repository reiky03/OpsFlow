<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $database = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? '');

        // Docker Compose exports the development PostgreSQL connection to every
        // backend process. Never let a local test run refresh that database.
        // CI may opt into the dedicated PostgreSQL database named opsflow_test.
        if ($database !== 'opsflow_test') {
            $this->setEnvironment('DB_CONNECTION', 'sqlite');
            $this->setEnvironment('DB_DATABASE', ':memory:');
            $this->setEnvironment('DB_URL', '');
        }

        $this->setEnvironment('APP_ENV', 'testing');

        $app = parent::createApplication();
        $connection = $app->make('config')->get('database.default');
        $configuredDatabase = $app->make('config')->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' && $configuredDatabase !== 'opsflow_test') {
            throw new LogicException('Refusing to run tests against a non-isolated database.');
        }

        return $app;
    }

    private function setEnvironment(string $key, string $value): void
    {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
