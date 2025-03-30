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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 100);
            $table->tinyInteger('priority')->default(1)->comment('0: low, 1: medium, 2: high');
            $table->text('description')->nullable();
            $table->date('exp')->nullable();
            $table->timestamps();
            
            $table->index('priority');
            $table->index('exp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};