<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pizza_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pizza_id')->constrained()->onDelete('cascade');
            $table->string('size_name'); 
            $table->integer('diameter'); 
            $table->integer('weight'); 
            $table->decimal('price', 8, 2); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pizza_sizes');
    }
};