<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();

            // personal information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('date_of_birth');
            $table->enum('sex', ['Male', 'Female']);
            $table->enum('civil_status', ['Single', 'Married', 'Divorced', 'Widowed', 'Separated'])->nullable();

            // contact and address
            $table->string('contact_number')->nullable();
            $table->string('purok')->nullable();
            $table->foreignId('barangay_id')->constrained('barangays')->restrictOnDelete();

            // beneficiary classification
            $table->string('sector');
            $table->boolean('is_familyhead')->default(false);

            // verification status
            $table->string('verification_status')->default('Pending');

            // uploaded identification document
            $table->string('id_document_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
