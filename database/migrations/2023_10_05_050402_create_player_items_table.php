<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayerItem extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_items', function (Blueprint $table) {

            //符号なし整数を用意
            $table->unsignedBigInteger('player_id')->comment("プレイヤーID");
            $table->unsignedBigInteger('item_id')->comment("アイテムID");

            //カウント用整数用意
            $table->integer('count')->default(0)->comment("所持");

            //タイムスタンプのような機能をテーブルに追加する
            $table->timestamps();
            
            //player_id と item_id の組み合わせが主キー
            $table->primary(['player_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //up メソッドで行った変更を元に戻す処理
        Schema::dropIfExists('player_items');
    }
}