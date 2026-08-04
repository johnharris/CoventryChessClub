<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests post directly to routes rather than through a rendered form, so
        // there is no CSRF token or matching origin header to send. The protection
        // itself is exercised by the browser, not here.
        $this->withoutMiddleware(PreventRequestForgery::class);
    }
}
