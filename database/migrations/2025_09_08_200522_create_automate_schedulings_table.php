<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomateSchedulingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automate_schedulings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_id')->nullable();            
            $table->tinyInteger('interval')->default(1)->nullable();
            $table->string('weekdays')->nullable();
            $table->string('weekday_time')->nullable();
            $table->tinyInteger('frequency_type')->default(0)->comment(
                '0 = Every Hour | 1 = N Hours | 2 = Daily | 3 = N Days | 4 = Weekly | 5 = Biweekly | 6 = Monthly | 7 = Bimonthly | 8 = Quarterly | 9 = Semi Annually | 10 = Annual | 11  = Specific Days | 12 = Once'
            );

            $table->boolean('perpetual')->default(0)->comment('0 = Off | 1 = On');
            $table->dateTime('start');
            $table->dateTime('end')->nullable();

            $table->boolean('status')->default(false)->comment('0 = InActive | 1 = Active');

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
        Schema::dropIfExists('automate_schedulings');
    }
}
