<?php

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

    // EN: Confirm the listing, complete details, and protected ID document are accessible; TL: Tiyaking naa-access ang listahan, detalye, at protektadong ID; Bisaya: Siguroa nga ma-access ang listahan, detalye, ug protektadong ID. -shaine
    public function test_beneficiary_details_are_available_to_authenticated_users(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('beneficiary-ids/id.pdf', 'verified test document');
        $beneficiary = $this->createBeneficiary('beneficiary-ids/id.pdf');

        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.index'))
            ->assertOk()
            ->assertSee('View/Action')
            ->assertSee(route('beneficiaries.show', $beneficiary));

        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.show', $beneficiary))
            ->assertOk()
            ->assertSee('Jane Beneficiary')
            ->assertSee('Pending')
            ->assertSee('View uploaded ID document')
            ->assertSee(route('beneficiaries.document', $beneficiary));

        $this->actingAs(User::factory()->create())
            ->get(route('beneficiaries.document', $beneficiary))
            ->assertOk();
    }

    // EN: Verify that an acknowledged review persists and appears in the listing; TL: Tiyaking nase-save at nakikita sa listahan ang status; Bisaya: Siguroa nga ma-save ug makita sa listahan ang status. -shaine
    public function test_beneficiary_can_be_verified_after_documents_are_confirmed(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('beneficiary-ids/id.pdf', 'verified test document');
        $beneficiary = $this->createBeneficiary('beneficiary-ids/id.pdf');

        $this->actingAs(User::factory()->create())
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'documents_checked' => '1',
            ])
            ->assertRedirect(route('beneficiaries.show', $beneficiary));

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'verification_status' => 'Verified',
        ]);

        $this->get(route('beneficiaries.index'))
            ->assertOk()
            ->assertSee('Verified');
    }

    // EN: Ensure login is required and other status changes are rejected; TL: Tiyaking kailangan ang login at tinatanggihan ang ibang status; Bisaya: Siguroa nga kinahanglan ang login ug isalikway ang ubang status. -shaine
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
                'documents_checked' => '1',
            ])
            ->assertSessionHasErrors('verification_status');

        $this->actingAs(User::factory()->create())
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'documents_checked' => '1',
            ])
            ->assertStatus(409);

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'verification_status' => 'Verified',
        ]);
    }

    // EN: Block verification when the acknowledgment or stored document is missing; TL: Harangin ang verification kung kulang ang kumpirmasyon o dokumento; Bisaya: Ayaw tugoti ang verification kon kulang ang kumpirmasyon o dokumento. -shaine
    public function test_verification_requires_document_confirmation_and_an_available_id(): void
    {
        Storage::fake('local');
        $beneficiary = $this->createBeneficiary();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('beneficiaries.show', $beneficiary))
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
            ])
            ->assertSessionHasErrors('documents_checked');

        $beneficiary->update(['id_document_path' => 'beneficiary-ids/missing.pdf']);

        $this->actingAs($user)
            ->from(route('beneficiaries.show', $beneficiary))
            ->patch(route('beneficiaries.update-verification', $beneficiary), [
                'verification_status' => 'Verified',
                'documents_checked' => '1',
            ])
            ->assertSessionHasErrors('documents_checked');

        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'verification_status' => 'Pending',
        ]);
    }

    // EN: Create one reusable Pending beneficiary for feature tests; TL: Gumawa ng reusable na Pending beneficiary para sa tests; Bisaya: Maghimo og reusable nga Pending beneficiary para sa mga test. -shaine
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
