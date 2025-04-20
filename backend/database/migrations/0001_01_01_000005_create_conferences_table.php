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
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('university_id')->constrained('universities', 'id')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('title');
            $table->string('slug')->unique();

            $table->text('description')->nullable();
            $table->string('location');
            $table->text('venue_details')->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->string('primary_color');
            $table->string('secondary_color');

            $table->boolean('is_latest')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};