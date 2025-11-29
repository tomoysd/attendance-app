<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('requested_clock_in_at')->nullable();
            $table->dateTime('requested_clock_out_at')->nullable();
            $table->text('reason');
            // ステータス（0:申請中,1:承認,2:却下）
            $table->tinyInteger('status')->default(0);
             // 承認した管理ユーザー
            $table->unsignedBigInteger('approved_by')->nullable();
            // 承認／却下日時
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            // 外部キー
            $table->foreign('attendance_id')
                ->references('id')->on('attendances')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('approved_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // インデックス
            $table->index(['user_id', 'status']);
            $table->index(['attendance_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
}
