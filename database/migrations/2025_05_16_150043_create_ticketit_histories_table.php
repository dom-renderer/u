<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketitHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketit_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('ticket_id')->unsigned()->nullable();
            $table->text('description')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->tinyInteger('type')->default(0)->comment('1 = Related to a schema');
            $table->string('model')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticketit_histories');
    }
}
