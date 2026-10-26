<?php

namespace Tests\Feature;

use App\Models\Beneficiary;
use App\Models\Payout;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            ->assertSee('<title>Sign In | MSWDO Kabacan</title>', false)
            ->assertSee('Sign in to the portal')
            ->assertSee('MSWDO Kabacan')
            ->assertSee('unlad-kabacan.jpg')
            ->assertSee('dswd-logo.png')
            ->assertSee('municipality-kabacan.png')
            ->assertSee('cotabato-province-seal.png')
            ->assertSee('dswd-bagong-pilipinas.png')
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
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your account has been created. Sign in to continue.');

        $this->assertDatabaseHas('users', [
            'name' => 'Test Staff',
            'email' => 'new.staff@dswd.gov.ph',
        ]);

        $user = User::where('email', 'new.staff@dswd.gov.ph')->firstOrFail();
        $this->assertNotSame('secure-password', $user->getRawOriginal('password'));
        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $this->post(route('login.submit'), [
            'email' => 'new.staff@dswd.gov.ph',
            'password' => 'secure-password',
        ])->assertRedirect(route('dashboard'));

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

    public function test_pages_without_persisted_beneficiary_records_do_not_show_fixtures(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.index'))
            ->assertOk()
            ->assertSee('No beneficiaries yet')
            ->assertDontSee('Juan Dela Cruz');

        $this->actingAs(User::factory()->create())
            ->get(route('verification.index'))
            ->assertOk()
            ->assertSee('Claimant Identity Check')
            ->assertSee('Live claimant');
    }

    public function test_claimant_identity_check_requires_camera_capture_and_local_id_review(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('verification.index'))
            ->assertOk()
            ->assertSee('Live claimant')
            ->assertSee('uploaded ID document')
            ->assertSee('Start camera')
            ->assertSee('Identity matches')
            ->assertSee('No facial recognition');
    }

    public function test_staff_identity_decision_is_saved_to_audit_log(): void
    {
        $this->assertFalse(Schema::hasColumn('identity_verification_logs', 'claim_reference'));
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('verification.review'), [
                'decision' => 'matched',
            ])
            ->assertRedirect(route('verification.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('identity_verification_logs', [
            'user_id' => $user->id,
            'decision' => 'matched',
        ]);

        $this->get(route('audit-logs.index'))
            ->assertOk()
            ->assertDontSee('Claim Reference')
            ->assertSee('Identity matches');
    }

    public function test_identity_decision_must_be_valid_before_it_is_logged(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('verification.index'))
            ->post(route('verification.review'), [
                'decision' => 'automatic_face_match',
            ])
            ->assertRedirect(route('verification.index'))
            ->assertSessionHasErrors('decision');

        $this->assertDatabaseCount('identity_verification_logs', 0);
    }

    public function test_beneficiary_can_be_created_and_exported(): void
    {
        $barangayId = DB::table('barangays')->insertGetId([
            'name' => 'Test Barangay',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('beneficiaries.store'), [
                'first_name' => 'Jamie',
                'last_name' => 'Rivera',
                'date_of_birth' => '1990-04-12',
                'sex' => 'Female',
                'barangay_id' => $barangayId,
                'sector' => 'General Beneficiary',
                'dependents' => 2,
            ])
            ->assertRedirect(route('beneficiaries.index'));

        $this->assertDatabaseHas('beneficiaries', [
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'verification_status' => 'Pending',
            'payout_status' => 'Pending',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('first_name,middle_name,last_name', $response->streamedContent());
        $this->assertStringContainsString('Jamie,,Rivera', $response->streamedContent());
    }

    public function test_beneficiary_csv_import_is_validated_and_persisted(): void
    {
        DB::table('barangays')->insert([
            'name' => 'Test Barangay',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $csv = "first_name,last_name,date_of_birth,sex,barangay,sector\nJamie,Rivera,1990-04-12,Female,Test Barangay,General Beneficiary\n";

        $this->actingAs(User::factory()->create())
            ->post(route('beneficiaries.import'), [
                'masterlist' => UploadedFile::fake()->createWithContent('masterlist.csv', $csv),
            ])
            ->assertRedirect(route('beneficiaries.index'))
            ->assertSessionHas('status', '1 beneficiaries imported.');

        $this->assertDatabaseHas('beneficiaries', [
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'verification_status' => 'Pending',
        ]);
    }

    public function test_invalid_beneficiary_csv_does_not_partially_import_records(): void
    {
        DB::table('barangays')->insert([
            'name' => 'Test Barangay',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $csv = "first_name,last_name,date_of_birth,sex,barangay,sector\nJamie,Rivera,1990-04-12,Female,Test Barangay,General Beneficiary\nAlex,Lee,not-a-date,Female,Unknown Barangay,General Beneficiary\n";

        $this->actingAs(User::factory()->create())
            ->from(route('beneficiaries.index'))
            ->post(route('beneficiaries.import'), [
                'masterlist' => UploadedFile::fake()->createWithContent('masterlist.csv', $csv),
            ])
            ->assertRedirect(route('beneficiaries.index'))
            ->assertSessionHasErrors('masterlist');

        $this->assertDatabaseCount('beneficiaries', 0);
    }

    public function test_programs_can_be_created_and_payout_releases_export_csv(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('programs.store'), [
                'name' => 'Community Support',
                'type' => 'Financial Assistance',
                'amount' => '2500.00',
                'coverage' => 'All barangays',
                'status' => 'Active',
            ])
            ->assertRedirect(route('programs.index'));

        $this->assertDatabaseHas('programs', [
            'name' => 'Community Support',
            'amount' => '2500.00',
            'status' => 'Active',
        ]);

        $barangayId = DB::table('barangays')->insertGetId([
            'name' => 'Test Barangay',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $beneficiary = Beneficiary::create([
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'date_of_birth' => '1990-04-12',
            'sex' => 'Female',
            'barangay_id' => $barangayId,
            'sector' => 'General Beneficiary',
        ]);
        $program = Program::where('name', 'Community Support')->firstOrFail();
        Payout::create([
            'reference' => 'PAY-TEST-001',
            'beneficiary_id' => $beneficiary->id,
            'program_id' => $program->id,
            'amount' => '2500.00',
            'status' => 'Released',
            'released_at' => now(),
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('payouts.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('reference,beneficiary,barangay,program', $csv);
        $this->assertStringContainsString('PAY-TEST-001,"Jamie Rivera","Test Barangay","Community Support",2500.00,Released', $csv);
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

    public function test_payout_page_does_not_display_unpersisted_records(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('payouts.index'))
            ->assertOk()
            ->assertSee('No payout records')
            ->assertDontSee('PAY-2026');
    }

    public function test_all_protected_pages_render_for_authenticated_staff(): void
    {
        $routes = [
            route('dashboard'),
            route('beneficiaries.index'),
            route('beneficiaries.create'),
            route('programs.create'),
            route('verification.index'),
            route('programs.index'),
            route('payouts.index'),
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
