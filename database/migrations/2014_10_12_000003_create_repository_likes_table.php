<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepositoryLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repository_likes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');;
            $table->bigInteger('repository_id')->unsigned();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->tinyInteger('like');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repository_likes', function($table) {
            $table->dropForeign('user_id');
            $table->dropForeign('repository_id');
        });

        Schema::dropIfExists('repository_likes');
    }
}
