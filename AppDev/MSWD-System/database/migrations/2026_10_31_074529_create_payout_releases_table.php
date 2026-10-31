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
        Schema::create('payout_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')
                    ->constrained('payouts')
                    ->restrictOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_releases');
    }
};
