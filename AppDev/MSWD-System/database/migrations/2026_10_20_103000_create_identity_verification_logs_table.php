<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('decision', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_verification_logs');
    }
};
