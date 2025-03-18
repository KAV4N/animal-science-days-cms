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
        Schema::create('conference_editors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conference_id')->constrained('conferences', 'id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users', 'id')->cascadeOnDelete();

            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['conference_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conference_editors');
    }
};
