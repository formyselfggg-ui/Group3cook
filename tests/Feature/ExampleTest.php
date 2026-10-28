<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_homepage_redirects_guests_to_the_application_sign_in(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
