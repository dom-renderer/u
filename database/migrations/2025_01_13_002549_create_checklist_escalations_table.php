<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistEscalationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_escalations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_checklist_id')->nullable();
            $table->boolean('type')->default(0)->comment('0 = Uncompletion | 1 = Completion');
            $table->tinyInteger('time')->default(1);
            $table->tinyInteger('time_type')->default(0)->comment('0 = Minutes | 1 = hour | 2 = Day');
            $table->tinyInteger('level')->default(0);
            $table->json('templates')->nullable();
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
        Schema::dropIfExists('checklist_escalations');
    }
}
