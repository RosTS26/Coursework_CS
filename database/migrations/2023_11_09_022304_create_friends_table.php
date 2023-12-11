<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendsTable extends Migration
{
    public function up()
    {
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('friends');
            $table->json('sent_app');
            $table->json('incoming_app');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id', 'friend_user_idx');
            $table->foreign('user_id', 'friend_user_fk')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('friends');
    }
}
