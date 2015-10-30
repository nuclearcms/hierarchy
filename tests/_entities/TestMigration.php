<?php

use Nuclear\Hierarchy\Contract\Migration\MigrationContract;

class TestMigration implements MigrationContract {

    /**
     * Run the migrations.
     */
    public function up()
    {
        throw new \Exception('up');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        return;
    }
}