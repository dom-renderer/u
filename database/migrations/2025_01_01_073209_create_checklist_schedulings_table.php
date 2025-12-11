<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistSchedulingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_schedulings', function (Blueprint $table) {
            $table->id();
            $table->string('notification_title')->nullable();
            $table->string('notification_description')->nullable();
            $table->unsignedBigInteger('checklist_id');
            $table->tinyInteger('branch_type')->nullable()->comment('1: Store, 2: Department, 3: Office');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('interval')->default(1)->nullable();
            $table->string('weekdays')->nullable();
            $table->string('weekday_time')->nullable();
            $table->tinyInteger('frequency_type')->default(0)->comment(
                '0 = Every Hour | 1 = N Hours | 2 = Daily | 3 = N Days | 4 = Weekly | 5 = Biweekly | 6 = Monthly | 7 = Bimonthly | 8 = Quarterly | 9 = Semi Annually | 10 = Annual | 11  = Specific Days | 12 = Once'
            );


            $table->boolean('perpetual')->default(0)->comment('0 = Off | 1 = On');
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->json('completion_data')->nullable();

            $table->tinyInteger('checker_branch_type')->nullable()->comment('1: Store, 2: Department, 3: Office');
            $table->unsignedBigInteger('checker_branch_id')->nullable();
            $table->unsignedBigInteger('checker_user_id')->nullable();

            $table->time('start_at')->nullable();
            $table->time('completed_by')->nullable();

            $table->boolean('do_not_allow_late_submission')->default(0)->nullable()->comment('1 = Late submission not allowed');

            $table->time('hours_required')->nullable();
            $table->time('start_grace_time')->nullable();
            $table->time('end_grace_time')->nullable();

            $table->boolean('allow_rescheduling')->default(0)->nullable();
            $table->boolean('is_import')->default(0)->nullable();

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
        Schema::dropIfExists('checklist_schedulings');
    }
}
