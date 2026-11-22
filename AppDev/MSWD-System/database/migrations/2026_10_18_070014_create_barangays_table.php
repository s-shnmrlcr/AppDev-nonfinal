<?php
// -shaine: Defines the barangay records referenced by beneficiaries.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // -shaine: Creates the table used to store barangay names.
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    // -shaine: Removes the barangay table when rolling back.
    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
