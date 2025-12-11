<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaDataToImageTimeStampPrintedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('image_time_stamp_printeds', function (Blueprint $table) {
            $table->string('timestamp')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('image_time_stamp_printeds', function (Blueprint $table) {
            $table->dropColumn(['timestamp', 'latitude', 'longitude', 'file']);
        });
    }
}
