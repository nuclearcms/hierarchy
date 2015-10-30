<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HierarchyCreateNodeFieldsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_fields', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('node_type_id')->unsigned()->nullable();

            $table->string('name');
            $table->string('label');
            $table->text('description');
            $table->double('position')->unsigned();
            $table->string('type');

            $table->timestamps();

            $table->foreign('node_type_id')
                ->references('id')
                ->on('node_types')
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
        Schema::drop('node_fields');
    }
}
