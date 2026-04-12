<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class F01Test extends TestCase
{
    use RefreshDatabase;

    public function test_placeholder()
    {
        $this->assertTrue(true);
    }
}
