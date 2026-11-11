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
        Schema::create('ivr_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('call_sid')->unique();
            $table->string('from_number')->nullable()->index();
            $table->string('to_number')->nullable();
            $table->string('direction')->nullable();
            $table->string('status')->nullable()->index();
            $table->string('selected_option')->nullable();
            $table->integer('duration')->nullable(); // seconds
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ivr_call_logs');
    }
};
