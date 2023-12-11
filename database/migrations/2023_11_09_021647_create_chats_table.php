<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('friend_id')->nullable();
            $table->json('chat');
            $table->json('new_msgs');
            $table->timestamps();
            $table->softDeletes();

            // Индексация user_id, чтоб ускорить поиск
            $table->index('user_id', 'chat_user_idx');
            // Привязываем таблицу по внешнему ключу user_id к родительской таблице users
            // onDelete('cascade') - при удалении элемента из родительской таблицы, удалем все связанные элементы в дочерней
            $table->foreign('user_id', 'chat_user_fk')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
}
