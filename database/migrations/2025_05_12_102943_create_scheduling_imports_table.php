<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulingImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduling_imports', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')->default(0)->comment('0 = Scheduling | 1 = User');
            $table->unsignedBigInteger('checklist_id')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('success')->nullable();
            $table->integer('error')->nullable();
            $table->boolean('status')->default(1)->comment('1 = Success | 2 = Error | 3 = Partial Success');
            $table->string('original_file')->nullable();
            $table->string('modified_file')->nullable();
            $table->json('response')->nullable();
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
        Schema::dropIfExists('scheduling_imports');
    }
}
