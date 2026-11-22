<?php
// -shaine: Stores references to captured and uploaded review images.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // -shaine: Adds private image-path columns to identity-review logs.
    public function up(): void
    {
        Schema::table('identity_verification_logs', function (Blueprint $table) {
            $table->string('captured_photo_path')->nullable();
            $table->string('id_image_path')->nullable();
        });
    }

    // -shaine: Removes the saved image-path columns from identity reviews.
    public function down(): void
    {
        Schema::table('identity_verification_logs', function (Blueprint $table) {
            $table->dropColumn(['captured_photo_path', 'id_image_path']);
        });
    }
};
