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
            $table->date('work_date');
            $table->dateTime('clock_in_at');
            $table->dateTime('clock_out_at');
            $table->dateTime('break1_start_at')->nullable();
            $table->dateTime('break1_end_at')->nullable();
            $table->dateTime('break2_start_at')->nullable();
            $table->dateTime('break2_end_at')->nullable();
            // 勤務区分（0:通常,1:有給,2:欠勤…など）
            $table->tinyInteger('work_type')->default(0);
            // ステータス（0:通常,1:修正申請中,2:修正済み…など）
            $table->tinyInteger('status')->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();

            // 外部キー＆インデックス
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->index(['user_id', 'work_date']);
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
