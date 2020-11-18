<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFiletwoRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filetwo_rows', function (Blueprint $table) {
            $table->id();
            $table->string('record_id', 255);
            $table->date('record_date');
            $table->string('event_name');
            $table->integer('number_of_events');
            $table->timestamps();
            $table->unique(['record_id', 'record_date', 'event_name'], 'f2_unq_cmpnd_k');
            $table->index('record_id');
            $table->foreign('record_id')->references('record_id')->on('fileone_rows');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('filetwo_rows');
    }
}
