<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('particular_id')->nullable();
            $table->unsignedBigInteger('issue_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('subject')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('field_name')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['pending', 'accepted', 'in_progress', 'closed'])->default('pending');
            $table->boolean('is_reopened')->default(false);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('new_tickets');
    }
}
