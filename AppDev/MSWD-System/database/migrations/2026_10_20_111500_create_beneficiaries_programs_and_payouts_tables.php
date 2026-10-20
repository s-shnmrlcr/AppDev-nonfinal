<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('date_of_birth');
            $table->string('sex', 20);
            $table->string('civil_status', 30)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('barangay_id')->constrained()->restrictOnDelete();
            $table->string('sector', 80);
            $table->string('household_id')->nullable()->index();
            $table->unsignedSmallInteger('household_members')->nullable();
            $table->unsignedSmallInteger('dependents')->nullable();
            $table->unsignedSmallInteger('children')->nullable();
            $table->unsignedSmallInteger('senior_citizens')->nullable();
            $table->unsignedSmallInteger('pwd_members')->nullable();
            $table->string('occupation')->nullable();
            $table->string('source_of_income')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->string('employment_status', 30)->nullable();
            $table->string('id_document_path')->nullable();
            $table->string('verification_status', 30)->default('Pending');
            $table->string('payout_status', 30)->default('Pending');
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type', 80);
            $table->decimal('amount', 12, 2);
            $table->string('coverage');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('Draft');
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('beneficiary_id')->constrained()->restrictOnDelete();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('Pending');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('beneficiaries');
    }
};
