<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPercentageColumnToTasksTable extends Migration
{
    public function up()
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('checklist_tasks', 'percentage')) {
                $table->double('percentage')->default(0)->nullable();
            }

            if (!Schema::hasColumn('checklist_tasks', 'extra_info')) {
                $table->json('extra_info')->nullable();
            }
        });

        $indexes = DB::select("SHOW INDEX FROM checklist_tasks");

        $indexNames = collect($indexes)->pluck('Key_name')->toArray();

        Schema::table('checklist_tasks', function (Blueprint $table) use ($indexNames) {

            if (!in_array('checklist_tasks_status_index', $indexNames)) {
                $table->index('status', 'checklist_tasks_status_index');
            }

            if (!in_array('checklist_tasks_percentage_index', $indexNames)) {
                $table->index('percentage', 'checklist_tasks_percentage_index');
            }
        });
    }

    public function down()
    {
        Schema::table('checklist_tasks', function (Blueprint $table) {

            if (Schema::hasColumn('checklist_tasks', 'percentage')) {
                $table->dropColumn('percentage');
            }

            if (Schema::hasColumn('checklist_tasks', 'extra_info')) {
                $table->dropColumn('extra_info');
            }

            $table->dropIndex('checklist_tasks_status_index');
            $table->dropIndex('checklist_tasks_percentage_index');
        });
    }
}
