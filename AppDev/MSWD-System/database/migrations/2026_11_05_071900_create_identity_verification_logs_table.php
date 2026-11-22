<?php
// -shaine: Defines the audit log for manual identity verification decisions.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // -shaine: Creates the audit table for claimant identity-review decisions.
    public function up(): void
    {
        Schema::create('identity_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('decision');
            $table->timestamps();
        });
    }

    // -shaine: Removes identity-review audit records when rolling back.
    public function down(): void
    {
        Schema::dropIfExists('identity_verification_logs');
    }
};
