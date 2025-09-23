<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('ja');
        config(['app.locale' => 'ja', 'app.fallback_locale' => 'ja']);
    }
}
