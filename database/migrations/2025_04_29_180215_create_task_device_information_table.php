<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskDeviceInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_device_information', function (Blueprint $table) {
            $table->id();
            $table->string('eloquent')->nullable();
            $table->unsignedBigInteger('eloquent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device_model')->nullable();
            $table->string('network_speed')->nullable();
            $table->string('device_version')->nullable();
            $table->boolean('resubmission')->default(0)->nullable();
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
        Schema::dropIfExists('task_device_information');
    }
}
