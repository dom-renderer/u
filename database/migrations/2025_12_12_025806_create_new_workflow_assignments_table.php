<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewWorkflowAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_workflow_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('new_workflow_template_id')->nullable()->comment('Reference to workflow template');
            $table->string('title')->nullable();
            $table->json('sections')->nullable()->comment('Workflow sections configuration');
            $table->text('description')->nullable();
            $table->datetime('start_from')->nullable()->comment('Start date and time for the workflow assignment');
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('added_by')->nullable();
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
        Schema::dropIfExists('new_workflow_assignments');
    }
}
