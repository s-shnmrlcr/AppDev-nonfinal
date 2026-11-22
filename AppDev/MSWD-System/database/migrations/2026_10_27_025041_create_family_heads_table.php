<?php
// -shaine: Defines household and income data for beneficiary family heads.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // -shaine: Creates household and economic detail storage for family heads.
    public function up(): void
    {
        Schema::create('family_heads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('beneficiary_id')
                ->unique()
                ->constrained('beneficiaries')
                ->cascadeOnDelete();

            $table->unsignedInteger('household_members')->nullable();
            $table->unsignedInteger('dependents')->nullable();
            $table->unsignedInteger('children')->nullable();
            $table->unsignedInteger('senior_citizens')->nullable();
            $table->unsignedInteger('pwd_members')->nullable();

            $table->string('occupation')->nullable();
            $table->string('source_of_income')->nullable();
            $table->decimal('monthly_estimated_income', 10, 2)->nullable();
            $table->string('employment_status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    // -shaine: Removes family-head details when rolling back.
    public function down(): void
    {
        Schema::dropIfExists('family_heads');
    }
};
