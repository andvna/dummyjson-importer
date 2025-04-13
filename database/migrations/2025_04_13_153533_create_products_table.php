<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Локальный ID в вашей базе
            $table->unsignedBigInteger('external_id')->unique(); // ID из dummyjson, уникальный
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2); // 8 знаков всего, 2 после запятой
            $table->decimal('discountPercentage', 5, 2)->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('stock')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('thumbnail')->nullable();
            // $table->json('images')->nullable(); // Можно хранить массив картинок как JSON
            $table->timestamps(); // Добавляет поля created_at и updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
