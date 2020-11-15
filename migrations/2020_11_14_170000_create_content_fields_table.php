<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentFieldsTable extends Migration
{

	/**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('content_type_id')->unsigned();

            $table->string('name');
            $table->string('label');
            $table->string('type');
            $table->text('description')->nullable();
            $table->integer('position')->unsigned();
            $table->integer('search_priority')->default(0);
            $table->boolean('visible');

            $table->text('rules')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable();
            
            $table->timestamps();

            $table->foreign('content_type_id')
                ->references('id')
                ->on('content_types')
                ->onDelete('cascade');
        });
    }

	/**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_fields');
    }
}