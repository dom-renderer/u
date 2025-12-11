<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeColumnsToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_uploads', function (Blueprint $table) {
            $table->boolean('perpetual')->default(false);
            $table->boolean('enable_store_access')->default(false);
            $table->boolean('enable_dom_access')->default(false);
            $table->boolean('enable_operation_manager_access')->default(false);
            $table->boolean('status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_uploads', function (Blueprint $table) {
            $table->dropColumn(['perpetual', 'enable_store_access', 'enable_dom_access', 'enable_operation_manager_access', 'status']);
        });
    }
}
