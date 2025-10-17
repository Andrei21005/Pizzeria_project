<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('pizzas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_url');
            $table->text('ingredients');
            $table->float('proteins')->nullable();
            $table->float('fats')->nullable();
            $table->float('carbohydrates')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pizzas');
    }
};
