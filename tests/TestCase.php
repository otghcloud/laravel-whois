<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use OTGH\LaravelWhois\LaravelWhoisServiceProvider;

/**
 * @property Application $app
 */
abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelWhoisServiceProvider::class,
        ];
    }
}
