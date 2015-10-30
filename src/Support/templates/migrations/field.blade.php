<?php echo '<?php'; ?>


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
        Schema::table('{{ $table }}', function (Blueprint $table)
        {
            $table->{{ $type }}('{{ $field }}')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('{{ $table }}', function (Blueprint $table)
        {
            $table->dropColumn('{{ $field }}');
        });
    }

}