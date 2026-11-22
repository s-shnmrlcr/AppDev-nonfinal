<?php
// -shaine: Adds staff remarks to beneficiary verification reviews.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // -shaine: Adds a column for staff notes on beneficiary review decisions.
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Store the staff's explanation for the review decision.
            $table->text('review_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    // -shaine: Removes the beneficiary review remarks column.
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            //
            $table->dropColumn('review_remarks');
        });
    }
};
