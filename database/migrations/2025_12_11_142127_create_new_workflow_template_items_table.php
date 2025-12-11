<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewWorkflowTemplateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_workflow_template_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('new_workflow_template_id')->nullable();
            $table->integer('step')->default(0);
            $table->string('step_name')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('checklist_id')->nullable();
            $table->text('checklist_description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->time('turn_around_time')->nullable();
            $table->boolean('trigger')->default(0)->comment('0 = Auto | 1 = Manual');
            $table->string('dependency')->default('ALL_COMPLETED')->comment('ALL_COMPLETED | ANY_COMPLETED | SELECTED_COMPLETED');
            $table->json('dependency_steps')->comment('WHEN dependency = SELECTED_COMPLETED, here will be step in an array');

            $table->string('section_id')->nullable()->comment('Section identifier');
            $table->string('section_name')->nullable()->comment('Section name');
            $table->string('section_code')->nullable()->comment('Section code');
            $table->text('section_description')->nullable()->comment('Section description');
            $table->integer('section_order')->nullable()->comment('Section order');
            $table->integer('step_order')->nullable()->comment('Step order within section');
            $table->boolean('is_entry_point')->default(false)->comment('Is this step an entry point');
            
            $table->unsignedBigInteger('maker_escalation_user_id')->nullable()->comment('Maker escalation user');
            $table->integer('maker_turn_around_time_day')->nullable()->comment('Maker turnaround time in days');
            $table->integer('maker_turn_around_time_hour')->nullable()->comment('Maker turnaround time in hours');
            $table->integer('maker_escalation_after_day')->nullable()->comment('Maker escalation after days');
            $table->integer('maker_escalation_after_hour')->nullable()->comment('Maker escalation after hours');
            $table->unsignedBigInteger('maker_escalation_email_notification')->nullable()->comment('Maker escalation email notification template');
            $table->unsignedBigInteger('maker_escalation_push_notification')->nullable()->comment('Maker escalation push notification template');
            
            $table->unsignedBigInteger('checker_id')->nullable()->comment('Checker user');
            $table->integer('checker_turn_around_time_day')->nullable()->comment('Checker turnaround time in days');
            $table->integer('checker_turn_around_time_hour')->nullable()->comment('Checker turnaround time in hours');
            
            $table->unsignedBigInteger('checker_escalation_user_id')->nullable()->comment('Checker escalation user');
            $table->integer('checker_escalation_after_day')->nullable()->comment('Checker escalation after days');
            $table->integer('checker_escalation_after_hour')->nullable()->comment('Checker escalation after hours');
            $table->unsignedBigInteger('checker_escalation_email_notification')->nullable()->comment('Checker escalation email notification template');
            $table->unsignedBigInteger('checker_escalation_push_notification')->nullable()->comment('Checker escalation push notification template');

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
        Schema::dropIfExists('new_workflow_template_items');
    }
}
