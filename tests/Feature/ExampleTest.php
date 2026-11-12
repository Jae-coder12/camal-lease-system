<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('backgroundlogo.jpg')
            ->assertSee('A Clearer Way to Find Your Place in the Market.')
            ->assertSee('Admin Login')
            ->assertSee('Total Buildings');
    }
}
