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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('token_id')->unique();
            $table->string('token_hash', 255)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->uuid('replaced_by_token_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
