<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stamp_correction_request_id');
            $table->dateTime('break_start_at');
            $table->dateTime('break_end_at');
            $table->timestamps();

            //外部キー
            $table->foreign('stamp_correction_request_id')
                ->references('id')->on('stamp_correction_requests')
                ->onDelete('cascade');

            $table->index(
                ['stamp_correction_request_id', 'break_start_at'],
                'scr_id_break_start_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stamp_correction_breaks');
    }
}
