<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_user_conference', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('media_id')->unique()->nullable()->constrained('media')->nullOnDelete(); 
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('conference_id')->constrained('conferences')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_user_conference');
    }
};