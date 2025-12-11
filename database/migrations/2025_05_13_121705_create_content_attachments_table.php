<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id');
            $table->enum('type', ['video', 'image', 'document']);
            $table->string('path')->nullable();
            $table->longText('description')->nullable();
            $table->tinyInteger('order')->default(0);
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
        Schema::dropIfExists('content_attachments');
    }
}
