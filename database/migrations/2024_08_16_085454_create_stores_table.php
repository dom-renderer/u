<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_type')->nullable();
            $table->unsignedBigInteger('model_type')->nullable();
            $table->string('name', 150);
            $table->string('code', 150)->nullable();
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->string('block', 150)->nullable();
            $table->string('street', 300)->nullable();
            $table->string('landmark', 300)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('location', 300)->nullable();
            $table->string('open_time', 200)->nullable();
            $table->string('close_time', 200)->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('location_url')->nullable();
            $table->string('map_latitude')->nullable();
            $table->string('map_longitude')->nullable();
            $table->boolean('status')->default(true)->comment('0 = inActive | 1 = Active');
            $table->unsignedBigInteger('city')->nullable();
            $table->unsignedBigInteger('dom_id')->nullable();
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
        Schema::dropIfExists('stores');
    }
}
