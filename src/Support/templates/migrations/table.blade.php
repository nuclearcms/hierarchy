<?php echo '<?php'; ?>

// WARNING! THIS IS A GENERATED FILE, PLEASE DO NOT EDIT!

namespace gen\Migrations;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Nuclear\Hierarchy\Contract\Migration\MigrationContract;

class {{ $migration }} extends Migration implements MigrationContract {

    /**
     * Run the migrations.
     */
    public function up()
    {
        \Schema::create('{{ $table }}', function (Blueprint $table)
        {
            $table->integer('id')->unsigned();

            $table->foreign('id')
                ->references('id')
                ->on('{{ config('hierarchy.nodesources_table', 'node_sources') }}')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        \Schema::drop('{{ $table }}');
    }

}