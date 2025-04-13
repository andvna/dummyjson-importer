<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Добавьте этот use

class ChangeReactionsColumnTypeInPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Используем сырой SQL-запрос для PostgreSQL с USING clause
        // Сначала приводим integer к text, затем text к json
        // Это более надежно обрабатывает NULL и числовые значения
        DB::statement('ALTER TABLE posts ALTER COLUMN reactions TYPE JSON USING reactions::text::json');

        // Если бы мы использовали Schema::table, это бы не сработало:
        // Schema::table('posts', function (Blueprint $table) {
        //     $table->json('reactions')->nullable()->change(); // Это вызывает ошибку в PgSQL
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Используем сырой SQL-запрос для отката
        // ВНИМАНИЕ: Это может привести к ошибке или потере данных,
        // если в JSON хранится что-то сложнее простого числа!
        try {
             // Пытаемся привести JSON к text, затем к integer
            DB::statement('ALTER TABLE posts ALTER COLUMN reactions TYPE INTEGER USING reactions::text::integer');
        } catch (\Illuminate\Database\QueryException $e) {
            // Если преобразование невозможно, просто меняем тип, данные могут стать некорректными
             DB::statement('ALTER TABLE posts ALTER COLUMN reactions TYPE INTEGER');
             // Логируем предупреждение
             Log::warning('Could not properly convert posts.reactions back to INTEGER during migration rollback. Data might be inconsistent.');
        }


        // Если бы мы использовали Schema::table:
        // Schema::table('posts', function (Blueprint $table) {
        //     $table->integer('reactions')->nullable()->change(); // Тоже может вызвать ошибку при откате
        // });
    }
}
