<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatePresetNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_preset_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_id')->nullable();
            $table->unsignedBigInteger('notification_template_id')->nullable();
            $table->tinyInteger('type')->default(1)->comment('
            
            1 == maker: 1 hour before starttime => cron
            2 == maker: not started after 30 min => cron
            3 == maker: 25% time before endtime => cron
            4 == maker: on reschedule => in code
            5 == checker: on approve => in code
            6 == checker: on rejection => in code
            7 == checker: completion => in code
            8 == maker: on reassignment => in code


            ');
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
        Schema::dropIfExists('template_preset_notifications');
    }
}
