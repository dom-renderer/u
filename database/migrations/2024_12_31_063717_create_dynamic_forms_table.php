<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('type')->default(0)->comment('0 = Inspection checklist | 1 = Workflow checklist');
            $table->json('schema');
            $table->integer('completion_time')->default(0);
            $table->tinyInteger('completion_time_type')->default(0)->comment('0 = Minutes | 1 = hour | 2 = Day');
            $table->tinyInteger('branch_type')->nullable()->comment('1: Store, 2: Department')->default(1);
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('in only workflow user id will be saved');
            $table->boolean('allow_double_rescheduling')->default(false);
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
        Schema::dropIfExists('dynamic_forms');
    }
}
