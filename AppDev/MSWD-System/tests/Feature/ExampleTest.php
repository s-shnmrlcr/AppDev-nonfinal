<?php

namespace Tests\Feature;

use App\Models\Beneficiary;
use App\Models\Payout;
use App\Models\PayoutRelease;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
            ->assertDontSee('Create account');
    }

    public function test_account_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_protected_pages_redirect_guests_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_sign_in_and_view_the_dashboard(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'Admin',
            'password' => 'prototype-password',
        ]);
        config(['mswd.admin_email' => 'admin@example.com']);

        $this->post(route('login.submit'), [
            'email' => 'admin@example.com',
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
            ->assertSee('Staff who verified')
            ->assertSee('No facial recognition');
    }

    public function test_staff_identity_decision_is_saved_to_audit_log(): void
    {
        $this->assertFalse(Schema::hasColumn('identity_verification_logs', 'claim_reference'));
        $user = User::factory()->create(['name' => 'Admin Account']);
        Storage::fake('local');
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

        $this->actingAs($user)
            ->post(route('verification.review'), [
                'decision' => 'matched',
                'staff_name' => 'Maria Santos',
                'beneficiary_id' => $beneficiary->id,
                'captured_photo' => UploadedFile::fake()->createWithContent(
                    'capture.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jf5kAAAAASUVORK5CYII=')
                ),
                'id_image' => UploadedFile::fake()->createWithContent(
                    'id.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jf5kAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect(route('verification.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('identity_verification_logs', [
            'user_id' => $user->id,
            'beneficiary_id' => $beneficiary->id,
            'decision' => 'matched',
        ]);
        $this->assertDatabaseHas('transaction_logs', [
            'user_id' => $user->id,
            'staff_name' => 'Maria Santos',
            'action' => 'Identity review recorded',
        ]);

        $auditContent = $this->get(route('audit-logs.index'))
            ->assertOk()
            ->assertDontSee('Claim Reference')
            ->assertSee('Identity matches')
            ->assertSee('Transaction History')
            ->getContent();
        $auditTables = strstr($auditContent, '<table class="data-table">');
        $this->assertNotFalse($auditTables);
        $this->assertStringNotContainsString('Maria Santos', $auditTables);

        $reviewId = DB::table('identity_verification_logs')
            ->where('beneficiary_id', $beneficiary->id)
            ->value('id');
        $this->get(route('verification.logs.show', $reviewId))
            ->assertOk()
            ->assertSee('Staff name')
            ->assertSee('Maria Santos');
    }

    public function test_identity_decision_must_be_valid_before_it_is_logged(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('verification.index'))
            ->post(route('verification.review'), [
                'decision' => 'automatic_face_match',
                'staff_name' => 'Maria Santos',
            ])
            ->assertRedirect(route('verification.index'))
            ->assertSessionHasErrors('decision');

        $this->assertDatabaseCount('identity_verification_logs', 0);
    }

    public function test_identity_review_requires_staff_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('verification.index'))
            ->post(route('verification.review'), [])
            ->assertRedirect(route('verification.index'))
            ->assertSessionHasErrors('staff_name');

        $this->assertDatabaseCount('identity_verification_logs', 0);
        $this->assertDatabaseCount('transaction_logs', 0);
    }

    public function test_beneficiary_can_be_created_and_exported(): void
    {
        $barangayId = DB::table('barangays')->insertGetId([
            'name' => 'Test Barangay',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create(['name' => 'Maria Santos']);
        $this->actingAs($user)
            ->post(route('beneficiaries.store'), [
                'first_name' => 'Jamie',
                'staff_name' => 'Maria Santos',
                'last_name' => 'Rivera',
                'date_of_birth' => '1990-04-12',
                'sex' => 'Female',
                'barangay_id' => $barangayId,
                'sector' => 'General Beneficiary',
                'is_familyhead' => 0,
            ])
            ->assertRedirect(route('beneficiaries.index'));

        $this->assertDatabaseHas('beneficiaries', [
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'verification_status' => 'Pending',
        ]);
        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => 'Maria Santos',
            'action' => 'Beneficiary registered',
            'subject_type' => 'Beneficiary',
            'subject_id' => Beneficiary::where('first_name', 'Jamie')->value('id'),
        ]);

        $beneficiary = Beneficiary::where('first_name', 'Jamie')->firstOrFail();
        $beneficiaryIndexContent = $this->actingAs($user)
            ->get(route('beneficiaries.index'))
            ->assertOk()
            ->getContent();
        $beneficiaryTable = strstr($beneficiaryIndexContent, '<table class="data-table" id="beneficiaryTable">');
        $this->assertNotFalse($beneficiaryTable);
        $this->assertStringNotContainsString('Maria Santos', $beneficiaryTable);
        $this->actingAs($user)
            ->get(route('beneficiaries.show', $beneficiary))
            ->assertSee('Registered By')
            ->assertSee('Maria Santos');

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
        $csv = "first_name,last_name,date_of_birth,sex,barangay,sector,is_familyhead\nJamie,Rivera,1990-04-12,Female,Test Barangay,General Beneficiary,0\n";

        $user = User::factory()->create(['name' => 'Maria Santos']);
        $this->actingAs($user)
            ->get(route('beneficiaries.index'))
            ->assertOk()
            ->assertDontSee('id="importStaffName"', false);

        $this->actingAs($user)
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
        $beneficiary = Beneficiary::where('first_name', 'Jamie')->firstOrFail();
        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => $user->name,
            'action' => 'Beneficiary registered',
            'subject_type' => 'Beneficiary',
            'subject_id' => $beneficiary->id,
            'details' => 'Imported from CSV.',
        ]);
    }

    public function test_invalid_beneficiary_csv_does_not_partially_import_records(): void
    {
        DB::table('barangays')->insert([
            'name' => 'Test Barangay',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $csv = "first_name,last_name,date_of_birth,sex,barangay,sector,is_familyhead\nJamie,Rivera,1990-04-12,Female,Test Barangay,General Beneficiary,0\nAlex,Lee,not-a-date,Female,Unknown Barangay,General Beneficiary,0\n";

        $this->actingAs(User::factory()->create())
            ->from(route('beneficiaries.index'))
            ->post(route('beneficiaries.import'), [
                'masterlist' => UploadedFile::fake()->createWithContent('masterlist.csv', $csv),
            ])
            ->assertRedirect(route('beneficiaries.index'))
            ->assertSessionHasErrors('masterlist');

        $this->assertDatabaseCount('beneficiaries', 0);
    }

    public function test_a_program_can_be_created_with_the_current_program_fields(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('programs.store'), [
                'staff_name' => 'Maria Santos',
                'program_name' => 'Emergency Assistance',
                'description' => 'Short-term support',
                'budget' => '2500.00',
                'start_date' => '2026-11-22',
                'end_date' => '2026-12-22',
                'status' => 'Active',
            ])
            ->assertRedirect(route('programs.index'))
            ->assertSessionHas('status', 'Payout program created.');

        $this->assertDatabaseHas('programs', [
            'program_name' => 'Emergency Assistance',
            'budget' => '2500.00',
        ]);
        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => 'Maria Santos',
            'action' => 'Program created',
            'details' => 'Emergency Assistance',
            'subject_type' => 'Program',
            'subject_id' => Program::where('program_name', 'Emergency Assistance')->value('id'),
        ]);
    }

    public function test_program_creation_requires_staff_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('programs.create'))
            ->post(route('programs.store'), [
                'program_name' => 'Emergency Assistance',
                'budget' => '2500.00',
                'status' => 'Active',
            ])
            ->assertRedirect(route('programs.create'))
            ->assertSessionHasErrors('staff_name');

        $this->assertDatabaseCount('programs', 0);
        $this->assertDatabaseCount('transaction_logs', 0);
    }

    public function test_program_update_and_delete_do_not_request_staff_name(): void
    {
        $program = Program::create([
            'program_name' => 'Community Support',
            'budget' => '1000.00',
            'status' => 'Active',
        ]);
        $user = User::factory()->create(['name' => 'Maria Santos']);

        $this->actingAs($user)
            ->get(route('programs.edit', $program))
            ->assertOk()
            ->assertDontSee('Staff name');

        $this->actingAs($user)
            ->put(route('programs.update', $program), [
                'program_name' => 'Community Support Updated',
                'description' => 'Updated description',
                'budget' => '1250.00',
                'status' => 'Active',
            ])
            ->assertRedirect(route('programs.index'));

        $this->actingAs($user)
            ->delete(route('programs.destroy', $program))
            ->assertRedirect(route('programs.index'));

        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => $user->name,
            'action' => 'Program updated',
            'subject_id' => $program->id,
        ]);
        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => $user->name,
            'action' => 'Program deleted',
            'subject_id' => $program->id,
        ]);
    }

    public function test_program_beneficiary_assignment_records_staff_name_per_payout(): void
    {
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
            'verification_status' => 'Verified',
        ]);
        $program = Program::create([
            'program_name' => 'Community Support',
            'budget' => '1000.00',
            'status' => 'Active',
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('programs.beneficiaries', $program))
            ->assertOk()
            ->assertSee('Staff name');

        $this->actingAs($user)
            ->get(route('programs.edit', $program))
            ->assertOk()
            ->assertDontSee('Staff name');

        $this->actingAs($user)
            ->from(route('programs.beneficiaries', $program))
            ->post(route('programs.beneficiaries.assign', $program), [
                'amount' => '250.00',
                'beneficiary_ids' => [$beneficiary->id],
            ])
            ->assertRedirect(route('programs.beneficiaries', $program))
            ->assertSessionHasErrors('staff_name');

        $this->assertDatabaseCount('payouts', 0);

        $this->actingAs($user)
            ->post(route('programs.beneficiaries.assign', $program), [
                'staff_name' => 'Maria Santos',
                'amount' => '250.00',
                'beneficiary_ids' => [$beneficiary->id],
            ])
            ->assertRedirect(route('programs.beneficiaries', $program));

        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => 'Maria Santos',
            'action' => 'Beneficiaries assigned to program',
            'subject_id' => $program->id,
        ]);

        $payout = Payout::where('program_id', $program->id)->firstOrFail();
        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => 'Maria Santos',
            'action' => 'Beneficiary assigned to program',
            'subject_type' => 'Payout',
            'subject_id' => $payout->id,
        ]);
        $this->actingAs($user)
            ->get(route('programs.beneficiaries', $program))
            ->assertSee('Assigned By')
            ->assertSee('Maria Santos');

        PayoutRelease::create([
            'payout_id' => $payout->id,
            'reference' => 'REL-TEST-LEGACY',
            'amount' => '250.00',
            'released_by' => $user->id,
            'status' => 'Released',
        ]);

        $this->actingAs($user)
            ->get(route('programs.beneficiaries', $program))
            ->assertOk()
            ->assertSee($user->name);
    }

    public function test_payout_release_requires_and_records_staff_name(): void
    {
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
        $program = Program::create([
            'program_name' => 'Community Support',
            'budget' => '1000.00',
            'status' => 'Active',
        ]);
        $payout = Payout::create([
            'reference' => 'PAY-TEST-STAFF',
            'beneficiary_id' => $beneficiary->id,
            'program_id' => $program->id,
            'amount' => '250.00',
            'status' => 'Pending',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('payout-releases.index', $payout))
            ->assertOk()
            ->assertSee('Staff name');

        $this->actingAs($user)
            ->from(route('payout-releases.index', $payout))
            ->post(route('payout-releases.store', $payout), [
                'amount' => '250.00',
            ])
            ->assertSessionHasErrors('staff_name');

        $this->assertDatabaseCount('payout_releases', 0);

        $this->actingAs($user)
            ->post(route('payout-releases.store', $payout), [
                'staff_name' => 'Maria Santos',
                'amount' => '250.00',
            ])
            ->assertRedirect(route('payout-releases.index', $payout));

        $this->assertDatabaseHas('transaction_logs', [
            'user_id' => $user->id,
            'staff_name' => 'Maria Santos',
            'action' => 'Payout release recorded',
        ]);

        $this->actingAs($user)
            ->get(route('payout-releases.index', $payout))
            ->assertOk()
            ->assertSee('Maria Santos');

        $this->actingAs($user)
            ->get(route('programs.beneficiaries', $program))
            ->assertOk()
            ->assertSee('Released By')
            ->assertSee('Maria Santos');
    }

    public function test_programs_can_be_created_and_payout_releases_export_csv(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('programs.store'), [
                'staff_name' => 'Maria Santos',
                'program_name' => 'Community Support',
                'description' => 'All barangays',
                'budget' => '2500.00',
                'status' => 'Active',
            ])
            ->assertRedirect(route('programs.index'));

        $this->assertDatabaseHas('programs', [
            'program_name' => 'Community Support',
            'budget' => '2500.00',
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
        $program = Program::where('program_name', 'Community Support')->firstOrFail();
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
