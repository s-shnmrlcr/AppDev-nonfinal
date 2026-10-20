<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // I-check una ang schema aron luwas kini sa database nga wala pa o nahuman na sa legacy change. -shaine
        if (Schema::hasColumn('identity_verification_logs', 'claim_reference')) {
            Schema::table('identity_verification_logs', function (Blueprint $table) {
                $table->dropIndex(['claim_reference', 'created_at']);
                $table->dropColumn('claim_reference');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('identity_verification_logs', 'claim_reference')) {
            // Kung i-rollback, ibalik ang column nga nullable kay wala nay reference values nga ma-restore. -shaine
            Schema::table('identity_verification_logs', function (Blueprint $table) {
                $table->string('claim_reference', 100)->nullable();
                $table->index(['claim_reference', 'created_at']);
            });
        }
    }
};
