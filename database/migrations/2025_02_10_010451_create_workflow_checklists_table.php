<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_template_id');
            $table->unsignedBigInteger('workflow_assignment_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('checklist_id');
            $table->tinyInteger('branch_type')->nullable()->comment('1: Store, 2: Department')->default(1);
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('in only workflow user id will be saved');
            $table->integer('completion_time')->default(0);
            $table->tinyInteger('completion_time_type')->default(0)->comment('0 = Minutes | 1 = hour | 2 = Day');
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
        Schema::dropIfExists('workflow_checklists');
    }
}
