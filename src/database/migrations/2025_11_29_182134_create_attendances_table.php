<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('clock_in_at');
            $table->dateTime('clock_out_at')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();

            // 外部キー
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            //検索用インデックス（ユーザー×日付で検索することが多い想定なら）
            $table->index(['user_id', 'clock_in_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
