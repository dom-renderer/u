<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPointChecklistOrNotInDynamicFormTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dynamic_forms', function (Blueprint $table) {
            $table->boolean('is_point_checklist')->default(false)->comment('1 = Point Checklist')->after('schema');
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
            $table->dropColumn('is_point_checklist');
        });
    }
}
