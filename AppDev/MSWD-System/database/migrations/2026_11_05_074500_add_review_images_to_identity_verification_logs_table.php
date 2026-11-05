<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_verification_logs', function (Blueprint $table) {
            $table->string('captured_photo_path')->nullable();
            $table->string('id_image_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('identity_verification_logs', function (Blueprint $table) {
            $table->dropColumn(['captured_photo_path', 'id_image_path']);
        });
    }
};
