<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'document_uploads', function (Blueprint $table) {
            $table->id();
            $table->string( 'file_name' );
            $table->unsignedBigInteger( 'document_id' );
            $table->unsignedBigInteger( 'location_category_id' );
            $table->unsignedBigInteger( 'location_id' );
            $table->date( 'expiry_date' )->nullable();
            $table->date( 'issue_date' )->nullable();
            $table->text( 'remark' )->nullable();
            $table->json( 'document_other' )->nullable();
            $table->date( 'remind_me_later_at' )->nullable();
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
        Schema::dropIfExists('document_uploads');
    }
}
