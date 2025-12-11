<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedoActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redo_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('field_id')->nullable()->comment('Class of field');
            $table->string('title')->nullable();
            $table->tinyInteger('page')->nullable();
            $table->text('remarks')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('completed_by')->nullable();
            
            $table->boolean('do_not_allow_late_submission')->default(0)->nullable()->comment('1 = Late submission not allowed');
            $table->tinyInteger('status')->default(0)->comment('0 = Pending | 1 = Completed');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redo_actions');
    }
}
