<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 100);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
