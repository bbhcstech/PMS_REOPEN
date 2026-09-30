<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DeveloperLoginTest extends TestCase
{
    public function test_developer_can_login_with_valid_credentials(): void
    {
        $dev = User::where('email', 'debu@gmail.com')->first();
        $this->assertNotNull($dev, 'Developer debu@gmail.com should exist');

        $response = $this->post('/login', [
            'email' => 'debu@gmail.com',
            'password' => '0eztaFx52z',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect(route('developer.dashboard'));
        $this->assertAuthenticatedAs($dev);
    }

    public function test_developer_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'debu@gmail.com',
            'password' => 'wrongpassword',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_developer_workspace(): void
    {
        $response = $this->get(route('developer.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_developer_accessing_developer_login_redirects_to_workspace(): void
    {
        $dev = User::where('email', 'debu@gmail.com')->first();
        $response = $this->actingAs($dev)->get('/login');
        $response->assertRedirect(route('developer.dashboard'));
    }

    public function test_authenticated_developer_can_view_workspace_dashboard(): void
    {
        $dev = User::where('email', 'debu@gmail.com')->first();
        $response = $this->actingAs($dev)->get(route('developer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee($dev->name);
    }
}
