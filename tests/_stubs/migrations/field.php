<?php
// WARNING! THIS IS A GENERATED FILE, PLEASE DO NOT EDIT!

namespace gen\Migrations;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Nuclear\Hierarchy\Contract\Migration\MigrationContract;

class HierarchyCreateDescriptionFieldForProjectSourceTable extends Migration implements MigrationContract {

    /**
     * Run the migrations.
     */
    public function up()
    {
        \Schema::table('ns_projects', function (Blueprint $table)
        {
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \Schema::table('ns_projects', function (Blueprint $table)
        {
            $table->dropColumn('description');
        });
    }

}