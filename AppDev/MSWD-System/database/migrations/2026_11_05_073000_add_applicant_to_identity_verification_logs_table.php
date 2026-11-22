<?php
// -shaine: Links identity verification records to their beneficiary applicants.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // -shaine: Adds the applicant reference and saved name to identity reviews.
    public function up(): void
    {
        Schema::table('identity_verification_logs', function (Blueprint $table) {
            $table->foreignId('beneficiary_id')
                ->nullable()
                ->after('user_id')
                ->constrained('beneficiaries')
                ->nullOnDelete();
            $table->string('applicant_name')->nullable()->after('beneficiary_id');
        });
    }

    // -shaine: Removes applicant details from identity-review logs.
    public function down(): void
    {
        Schema::table('identity_verification_logs', function (Blueprint $table) {
            $table->dropForeign(['beneficiary_id']);
            $table->dropColumn(['beneficiary_id', 'applicant_name']);
        });
    }
};
