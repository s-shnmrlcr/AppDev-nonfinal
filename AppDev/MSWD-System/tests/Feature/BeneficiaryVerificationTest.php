<?php
// -shaine: Tests beneficiary review access, decisions, and document handling.

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BeneficiaryVerificationTest extends TestCase
{
    use RefreshDatabase;

    // -shaine: Confirms authenticated staff can access beneficiary details and protected ID files.
    public function test_beneficiary_details_are_available_to_authenticated_users(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('beneficiary-ids/id.pdf', 'verified test document');
        $beneficiary = $this->createBeneficiary('beneficiary-ids/id.pdf');

        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.index'))
            ->assertOk()
            ->assertSee('Action')
            ->assertSee(route('beneficiaries.show', $beneficiary));

        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.show', $beneficiary))
            ->assertOk()
            ->assertSee('Jane Beneficiary')
            ->assertSee('Pending')
            ->assertSee('View uploaded ID document')
            ->assertDontSee('name="staff_name"', false)
            ->assertSee(route('beneficiaries.document', $beneficiary));

        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.document', $beneficiary))
            ->assertOk();
    }

    // -shaine: Ensures new beneficiary registration requires a staff name.
    public function test_new_beneficiary_registration_requires_a_staff_name(): void
    {
        $barangay = Barangay::create(['name' => 'Poblacion']);

        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('beneficiaries.create'))
            ->assertOk()
            ->assertSee('Staff name');

        $this->actingAs($user)
            ->from(route('beneficiaries.create'))
            ->post(route('beneficiaries.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Beneficiary',
                'date_of_birth' => '1990-01-01',
                'sex' => 'Female',
                'barangay_id' => $barangay->id,
                'sector' => 'General Beneficiary',
                'is_familyhead' => 0,
            ])
            ->assertRedirect(route('beneficiaries.create'))
            ->assertSessionHasErrors('staff_name');

        $this->assertDatabaseCount('beneficiaries', 0);
        $this->assertDatabaseCount('transaction_logs', 0);
    }

    // -shaine: Confirms reviewed beneficiary status and remarks persist and appear.
    public function test_beneficiary_can_be_verified_after_documents_are_confirmed(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('beneficiary-ids/id.pdf', 'verified test document');
        $beneficiary = $this->createBeneficiary('beneficiary-ids/id.pdf');

        $user = User::factory()->create(['name' => 'Maria Santos']);
        $this->actingAs($user)
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'personal_info_checked' => '1',
                'address_checked' => '1',
                'classification_checked' => '1',
                'documents_checked' => '1',
            ])
            ->assertRedirect(route('beneficiaries.show', $beneficiary));

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'verification_status' => 'Verified',
        ]);
        $this->assertDatabaseHas('transaction_logs', [
            'staff_name' => $user->name,
            'action' => 'Beneficiary verification reviewed',
            'subject_id' => $beneficiary->id,
        ]);

        $this->get(route('beneficiaries.index'))
            ->assertOk()
            ->assertSee('Verified');
    }

    // -shaine: Confirms beneficiary edits do not ask for registration staff attribution.
    public function test_beneficiary_update_does_not_request_a_staff_name(): void
    {
        $beneficiary = $this->createBeneficiary();
        $user = User::factory()->create(['name' => 'Maria Santos']);

        $this->actingAs($user)
            ->get(route('beneficiaries.edit', $beneficiary))
            ->assertOk()
            ->assertDontSee('Staff name');

        $this->actingAs($user)
            ->put(route('beneficiaries.update', $beneficiary), [
                'first_name' => 'Jane',
                'last_name' => 'Updated',
                'date_of_birth' => '1990-01-01',
                'sex' => 'Female',
                'barangay_id' => $beneficiary->barangay_id,
                'sector' => 'General Beneficiary',
                'is_familyhead' => 0,
            ])
            ->assertRedirect(route('beneficiaries.index'));

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'last_name' => 'Updated',
        ]);
        $this->assertDatabaseHas('transaction_logs', [
            'user_id' => $user->id,
            'staff_name' => $user->name,
            'action' => 'Beneficiary updated',
            'subject_id' => $beneficiary->id,
        ]);
    }

    // -shaine: Confirms deleting a beneficiary does not request a form staff name.
    public function test_beneficiary_delete_does_not_request_a_staff_name(): void
    {
        $beneficiary = $this->createBeneficiary();
        $user = User::factory()->create(['name' => 'Maria Santos']);

        $this->actingAs($user)
            ->delete(route('beneficiaries.destroy', $beneficiary))
            ->assertRedirect(route('beneficiaries.index'));

        $this->assertDatabaseMissing('beneficiaries', ['id' => $beneficiary->id]);
        $this->assertDatabaseHas('transaction_logs', [
            'user_id' => $user->id,
            'staff_name' => $user->name,
            'action' => 'Beneficiary deleted',
            'subject_id' => $beneficiary->id,
        ]);
    }

    // -shaine: Protects the verification endpoint and rejects invalid status transitions.
    public function test_verification_action_is_protected_and_cannot_change_other_statuses(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('beneficiary-ids/id.pdf', 'verified test document');
        $beneficiary = $this->createBeneficiary('beneficiary-ids/id.pdf');

        $this->get(route('beneficiaries.show', $beneficiary))
            ->assertRedirect(route('login'));

        $beneficiary->update(['verification_status' => 'Verified']);

        $this->actingAs(User::factory()->create())
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Pending',
                'personal_info_checked' => '1',
                'address_checked' => '1',
                'classification_checked' => '1',
                'documents_checked' => '1',
            ])
            ->assertStatus(409);

        $this->actingAs(User::factory()->create())
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'personal_info_checked' => '1',
                'address_checked' => '1',
                'classification_checked' => '1',
                'documents_checked' => '1',
            ])
            ->assertStatus(409);

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'verification_status' => 'Verified',
        ]);
    }

    // -shaine: Requires each checklist acknowledgment and an available ID document.
    public function test_verification_requires_document_confirmation_and_an_available_id(): void
    {
        Storage::fake('local');
        $beneficiary = $this->createBeneficiary();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('beneficiaries.show', $beneficiary))
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'personal_info_checked' => '1',
                'address_checked' => '1',
                'classification_checked' => '1',
            ])
            ->assertSessionHasErrors('documents_checked');

        $beneficiary->update(['id_document_path' => 'beneficiary-ids/missing.pdf']);

        $this->actingAs($user)
            ->from(route('beneficiaries.show', $beneficiary))
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'personal_info_checked' => '1',
                'address_checked' => '1',
                'classification_checked' => '1',
                'documents_checked' => '1',
            ])
            ->assertSessionHasErrors('documents_checked');

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'verification_status' => 'Pending',
        ]);
    }

    // -shaine: Confirms verification uses the signed-in account rather than a staff form field.
    public function test_verification_does_not_request_a_staff_name(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('beneficiary-ids/id.pdf', 'verified test document');
        $beneficiary = $this->createBeneficiary('beneficiary-ids/id.pdf');

        $user = User::factory()->create(['name' => 'Maria Santos']);
        $this->actingAs($user)
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'personal_info_checked' => '1',
                'address_checked' => '1',
                'classification_checked' => '1',
                'documents_checked' => '1',
            ])
            ->assertRedirect(route('beneficiaries.show', $beneficiary));

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'verification_status' => 'Verified',
        ]);
        $this->assertDatabaseHas('transaction_logs', [
            'user_id' => $user->id,
            'staff_name' => $user->name,
            'action' => 'Beneficiary verification reviewed',
            'subject_id' => $beneficiary->id,
        ]);
    }

    // -shaine: Creates a pending beneficiary fixture with an optional stored document.
    private function createBeneficiary(?string $documentPath = null): Beneficiary
    {
        $barangay = Barangay::create(['name' => 'Poblacion']);

        return Beneficiary::create([
            'first_name' => 'Jane',
            'last_name' => 'Beneficiary',
            'date_of_birth' => '1990-01-01',
            'sex' => 'Female',
            'barangay_id' => $barangay->id,
            'sector' => 'General Beneficiary',
            'id_document_path' => $documentPath,
        ]);
    }
}
