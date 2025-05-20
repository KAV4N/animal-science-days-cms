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
        Schema::create('page_menus', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('conference_id')->constrained('conferences', 'id')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('slug');
            
            $table->integer('order')->default(0);

            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['conference_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_menus');
    }
};
