<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
           $table->id();
           $table->string('name')->nullable();
           $table->string('middle_name')->nullable();
           $table->string('last_name')->nullable();
           $table->string('profile')->nullable();
           $table->string('phone_number')->nullable();
           $table->string('email')->unique();
           $table->boolean('status')->default(true)->comment('0 = InActive | 1 = Active');
           $table->string('username')->unique();
           $table->timestamp('email_verified_at')->nullable();
           $table->string('password');
           $table->string('employee_id')->nullable();
           $table->softDeletes();
           $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
