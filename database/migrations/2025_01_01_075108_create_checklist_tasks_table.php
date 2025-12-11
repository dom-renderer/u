<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_tasks', function (Blueprint $table) {
            $table->id();
            $table->boolean('type')->default(0)->comment('0 = Inspection Checklist Scheduling | 1 = Workflow Checklist Scheduling');
            $table->unsignedBigInteger('checklist_scheduling_id')->nullable();
            $table->unsignedBigInteger('workflow_checklist_id')->nullable();
            $table->string('code')->nullable();
            $table->dateTime('date');
            $table->dateTime('completion_date')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 = Pending | 1 = In-Progeress | 2 = Done');
            $table->json('form')->nullable();
            $table->json('data')->nullable();
            $table->dateTime('started_at')->nullable();
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
        Schema::dropIfExists('checklist_tasks');
    }
}
