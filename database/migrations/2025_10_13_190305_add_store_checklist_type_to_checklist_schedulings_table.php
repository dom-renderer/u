<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreChecklistTypeToChecklistSchedulingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklist_schedulings', function (Blueprint $table) {
            $table->boolean('import_type')->default(false)->comment('0 = Default | 1 = Store Import');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checklist_schedulings', function (Blueprint $table) {
            $table->dropColumn('import_type');
        });
    }
}
