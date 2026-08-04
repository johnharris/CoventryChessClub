<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Binds Laravel's TestCase to every test in the Feature directory, so tests
| can use the framework's helpers (`$this->get()`, `actingAs()` and so on).
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature');
