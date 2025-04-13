<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

 
class AddDummyjsonFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
 
        Schema::table('users', function (Blueprint $table) {
 
            $table->unsignedBigInteger('external_id')->unique()->nullable()->after('id'); // nullable, если не все юзеры будут из API

 
            $table->string('first_name')->nullable()->after('name'); // Имя
            $table->string('last_name')->nullable()->after('first_name'); // Фамилия
            $table->string('username')->unique()->nullable()->after('email'); // Имя пользователя (уникальное?), nullable на всякий случай
            $table->string('phone')->nullable()->after('username'); // Телефон
            $table->string('image_url')->nullable()->after('phone'); // Ссылка на аватар (из поля 'image' API)

 
 
 
            $table->string('name')->nullable()->change();
            $table->string('password')->nullable()->change(); // Пароль из API не берем!
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
 
            $table->dropColumn(['image_url', 'phone', 'username', 'last_name', 'first_name', 'external_id']);

 
 
 
 
 
 
        });
    }
}
