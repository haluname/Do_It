<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('week');
            $table->integer('year');
            $table->text('content');
            $table->timestamps();
            
            $table->unique(['user_id', 'week', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}