<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservedEscalationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserved_escalations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('escalation_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->dateTime('date');
            $table->boolean('sent')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('reserved_escalations');
    }
}
