<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeleteFrequencyToDynamicFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->enum('remove_media_frequency', ['never', 'every_n_day'])->default('never');
            $table->smallInteger('remove_media_frequency_after_n_day')->default(365);
            $table->boolean('is_store_checklist')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->dropColumn(['remove_media_frequency', 'remove_media_frequency_after_n_day', 'is_store_checklist']);
        });
    }
}
