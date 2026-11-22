<?php
// -shaine: Defines beneficiary assignments and their program payout records.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // -shaine: Creates payout assignments linking beneficiaries to programs.
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('beneficiary_id')
                    ->constrained('beneficiaries')
                    ->restrictOnDelete();
            $table->foreignId('program_id')
                    ->constrained('programs')
                    ->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('Pending');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->unique(['beneficiary_id', 'program_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    // -shaine: Removes payout assignments when rolling back.
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
