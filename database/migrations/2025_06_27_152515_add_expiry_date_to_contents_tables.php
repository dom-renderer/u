<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiryDateToContentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->integer('ordering')->default(10000)->after('status');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dateTime('expiry_date')->nullable()->after('added_by');
            $table->integer('ordering')->default(10000)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('ordering');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'ordering']);
        });
    }
}
