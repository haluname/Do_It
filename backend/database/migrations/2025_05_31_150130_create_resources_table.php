<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTable extends Migration
{
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['book', 'guide', 'faq']);
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author')->nullable();
            $table->integer('year')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('file_path')->nullable(); // Percorso del file
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('resources');
    }
}
