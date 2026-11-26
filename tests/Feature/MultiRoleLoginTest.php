<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class MultiRoleLoginTest extends TestCase
{
    public function test_superadmin_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'superadmin@bbhpms.com',
            'password' => 'SuperAdmin@123',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_hr_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'hr@company.com',
            'password' => 'Hr@123456789',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_developer_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'rajat@gmail.com',
            'password' => '2vpzsbBiPl',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect(route('developer.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_company_admin_can_login_via_company_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'bit@gmail.com',
            'password' => '12345678',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_company_admin_can_login_via_user_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => 'Admin@123456789',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_hr_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'hr@company.com',
            'password' => 'wrong_password_123',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
