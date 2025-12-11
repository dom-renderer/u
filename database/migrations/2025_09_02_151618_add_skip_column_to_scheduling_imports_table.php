<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkipColumnToSchedulingImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduling_imports', function (Blueprint $table) {
            $table->integer('skip')->default(0)->nullable()->after('error');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduling_imports', function (Blueprint $table) {
            $table->dropColumn('skip');
        });
    }
}
