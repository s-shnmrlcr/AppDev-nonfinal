<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_homepage_redirects_to_the_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_the_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to the portal')
            ->assertSee(route('register'));
    }

    public function test_the_registration_page_renders(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee(route('register.store'));
    }

    public function test_authorized_staff_can_create_an_account(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Test Staff',
            'email' => 'NEW.STAFF@DSWD.GOV.PH',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'Test Staff',
            'email' => 'new.staff@dswd.gov.ph',
        ]);

        $user = User::where('email', 'new.staff@dswd.gov.ph')->firstOrFail();
        $this->assertNotSame('secure-password', $user->getRawOriginal('password'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_registration_requires_an_authorized_email_and_matching_passwords(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Outside User',
                'email' => 'person@example.com',
                'password' => 'secure-password',
                'password_confirmation' => 'secure-password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Test Staff',
                'email' => 'staff@dswd.gov.ph',
                'password' => 'secure-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_protected_pages_redirect_guests_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_can_sign_in_and_view_the_dashboard(): void
    {
        User::factory()->create([
            'email' => 'staff@dswd.gov.ph',
            'password' => 'prototype-password',
        ]);

        $this->post(route('login.submit'), [
            'email' => 'staff@dswd.gov.ph',
            'password' => 'prototype-password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_staff_can_sign_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_beneficiary_and_verification_routes_show_the_requested_record(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.show', 'BEN-00002'))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('March 22, 1984');

        $this->actingAs(User::factory()->create())
            ->get(route('verification.index', 'BEN-00002'))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('Pending');
    }

    public function test_unknown_detail_routes_return_not_found(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/beneficiaries/does-not-exist')
            ->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get('/payouts/does-not-exist')
            ->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get('/verification/does-not-exist')
            ->assertNotFound();
    }

    public function test_only_payouts_ready_for_release_can_be_confirmed(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('payouts.show', 'PAY-2026-000182'))
            ->assertOk()
            ->assertSee('Confirm Release');

        $this->actingAs(User::factory()->create())
            ->get(route('payouts.show', 'PAY-2026-000184'))
            ->assertOk()
            ->assertSee('Released')
            ->assertDontSee('Confirm Release');

        $this->actingAs(User::factory()->create())
            ->get(route('payouts.show', 'PAY-2026-000181'))
            ->assertOk()
            ->assertSee('Blocked')
            ->assertDontSee('Confirm Release');
    }

    public function test_all_protected_pages_render_for_authenticated_staff(): void
    {
        $routes = [
            route('dashboard'),
            route('beneficiaries.index'),
            route('beneficiaries.create'),
            route('beneficiaries.show', 'BEN-00001'),
            route('verification.index'),
            route('programs.index'),
            route('payouts.index'),
            route('payouts.show', 'PAY-2026-000184'),
            route('reports.index'),
            route('audit-logs.index'),
            route('users.index'),
            route('settings.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs(User::factory()->create())
                ->get($url)
                ->assertOk();
        }
    }
}
