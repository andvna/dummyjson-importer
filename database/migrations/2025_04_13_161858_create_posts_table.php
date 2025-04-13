<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration // Убедитесь, что имя класса совпадает с именем файла
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // Локальный ID
            $table->unsignedBigInteger('external_id')->unique(); // 'id' из API
            $table->string('title'); // 'title' из API
            $table->text('body'); // 'body' из API
            $table->unsignedBigInteger('user_external_id')->nullable()->index(); // 'userId' из API, ссылается на внешний ID пользователя
            $table->json('tags')->nullable(); // 'tags' из API (массив строк) - храним как JSON
            $table->integer('reactions')->nullable(); // 'reactions' из API (число)
 
 
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
        Schema::dropIfExists('posts');
    }
}
