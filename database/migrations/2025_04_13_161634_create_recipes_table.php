<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipesTable extends Migration // Убедитесь, что имя класса совпадает с именем файла
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id(); // Локальный ID
            $table->unsignedBigInteger('external_id')->unique(); // ID из dummyjson
            $table->string('name'); // 'name' из API
            $table->json('ingredients'); // 'ingredients' из API (массив строк) - храним как JSON
            $table->json('instructions'); // 'instructions' из API (массив строк) - храним как JSON
            $table->integer('prep_time_minutes')->nullable(); // 'prepTimeMinutes'
            $table->integer('cook_time_minutes')->nullable(); // 'cookTimeMinutes'
            $table->integer('servings')->nullable(); // 'servings'
            $table->string('difficulty')->nullable(); // 'difficulty'
            $table->string('cuisine')->nullable(); // 'cuisine'
            $table->integer('calories_per_serving')->nullable(); // 'caloriesPerServing'
            $table->decimal('rating', 3, 2)->nullable(); // 'rating'
            $table->string('image_url')->nullable(); // 'image' из API
 
 
 
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipes');
    }
}
