<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaintenanceStatusToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('maintenance_mode')->default(false);
        });

        Schema::table('new_tickets', function (Blueprint $table) {
            $table->dateTime('in_progress_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('maintenance_mode');
        });

        Schema::table('new_tickets', function (Blueprint $table) {
            $table->dropColumn('in_progress_at');
        });
    }
}
