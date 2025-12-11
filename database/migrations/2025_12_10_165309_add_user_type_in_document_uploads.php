<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTypeInDocumentUploads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_users', function (Blueprint $table) {
            $table->boolean('user_type')->default(0)->nullable()->comment('0 = Notificaiton Users | 1 = Access Users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_users', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
}
