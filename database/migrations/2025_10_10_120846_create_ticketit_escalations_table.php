<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketitEscalationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketit_escalations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->integer('escalation_level')->default(0);
            $table->time('escalation_fire_time')->comment('00:00:00 = Day:Hour:minute')->nullable();
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
        Schema::dropIfExists('ticketit_escalations');
    }
}
