<?php

namespace Esign\Linkable\Tests;

use Esign\Linkable\LinkableServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LinkableServiceProvider::class];
    }
} 