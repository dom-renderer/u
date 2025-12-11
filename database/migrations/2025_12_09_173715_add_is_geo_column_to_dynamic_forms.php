<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsGeoColumnToDynamicForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->boolean('is_geofencing_enabled')->default(false)->nullable();
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
            $table->dropColumn('is_geofencing_enabled');
        });
    }
}
