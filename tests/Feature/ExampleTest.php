<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example. The root route redirects to SSO login for unauthenticated users.
     */
    public function test_the_application_returns_a_redirect(): void
    {
        $response = $this->get('/');

        // Root route redirects to SSO login
        $response->assertStatus(302);
    }
}
