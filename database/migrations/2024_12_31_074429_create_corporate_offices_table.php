<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporateOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporate_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('address1');
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
        Schema::dropIfExists('corporate_offices');
    }
}
