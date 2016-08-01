<?php

use Nuclear\Hierarchy\Builders\MigrationBuilder;
use org\bovigo\vfs\vfsStream;

class MigrationBuilderTest extends TestBase {

    protected function getBuilder()
    {
        return new MigrationBuilder;
    }

    /** @test */
    function it_creates_source_table_migration()
    {
        $builder = $this->getBuilder();

        $this->assertFileNotExists(
            $builder->getMigrationPath('project')
        );

        $classPath = $builder->buildSourceTableMigration('project');

        $this->assertFileExists(
            $builder->getMigrationPath('project')
        );

        $this->assertFileEquals(
            $builder->getMigrationPath('project'),
            dirname(__DIR__) . '/_stubs/migrations/table.php'
        );

        $this->assertEquals(
            $classPath,
            $builder->getMigrationClassPath(
                $builder->getTableMigrationName('project')
            )
        );
    }

    /** @test */
    function it_destroys_source_table_migration()
    {
        $builder = $this->getBuilder();

        $builder->buildSourceTableMigration('project');
        $builder->buildFieldMigrationForTable('area', 'integer', false, 'project');
        $builder->buildFieldMigrationForTable('description', 'text', false, 'project');

        $this->assertFileExists(
            $builder->getMigrationPath('project')
        );
        $this->assertFileExists(
            $builder->getMigrationPath('project', 'area')
        );
        $this->assertFileExists(
            $builder->getMigrationPath('project', 'description')
        );

        $builder->destroySourceTableMigration('project', ['area', 'description']);

        $this->assertFileNotExists(
            $builder->getMigrationPath('project')
        );
        $this->assertFileNotExists(
            $builder->getMigrationPath('project', 'area')
        );
        $this->assertFileNotExists(
            $builder->getMigrationPath('project', 'description')
        );
    }

    /** @test */
    function it_creates_field_migration_for_table()
    {
        $builder = $this->getBuilder();

        $this->assertFileNotExists(
            $builder->getMigrationPath('project', 'description')
        );

        $classPath = $builder->buildFieldMigrationForTable('description', 'textarea', false, 'project');

        $this->assertFileExists(
            $builder->getMigrationPath('project', 'description')
        );

        $this->assertFileEquals(
            $builder->getMigrationPath('project', 'description'),
            dirname(__DIR__) . '/_stubs/migrations/field.php'
        );

        $this->assertEquals(
            $classPath,
            $builder->getMigrationClassPath(
                $builder->getTableFieldMigrationName('description', 'project')
            )
        );
    }

    /** @test */
    function it_creates_field_migration_for_table_with_index()
    {
        $builder = $this->getBuilder();

        $this->assertFileNotExists(
            $builder->getMigrationPath('project', 'location')
        );

        $classPath = $builder->buildFieldMigrationForTable('location', 'text', true, 'project');

        $this->assertFileExists(
            $builder->getMigrationPath('project', 'location')
        );

        $this->assertFileEquals(
            $builder->getMigrationPath('project', 'location'),
            dirname(__DIR__) . '/_stubs/migrations/field_index.php'
        );

        $this->assertEquals(
            $classPath,
            $builder->getMigrationClassPath(
                $builder->getTableFieldMigrationName('location', 'project')
            )
        );
    }

    /** @test */
    function it_destroys_field_migration_for_table()
    {
        $builder = $this->getBuilder();

        $builder->buildFieldMigrationForTable('area', 'integer', false, 'project');

        $this->assertFileExists(
            $builder->getMigrationPath('project', 'area')
        );

        $builder->destroyFieldMigrationForTable('area', 'project');

        $this->assertFileNotExists(
            $builder->getMigrationPath('project', 'area')
        );
    }

    /** @test */
    function it_returns_table_migration_name()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            'HierarchyCreateProjectSourceTable',
            $builder->getTableMigrationName('project')
        );
    }

    /** @test */
    function it_returns_table_field_migration_name()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            'HierarchyCreateDateFieldForProjectSourceTable',
            $builder->getTableFieldMigrationName('date', 'project')
        );
    }

    /** @test */
    function it_returns_the_migration_base_path()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            vfsStream::url('gen/Migrations'),
            $builder->getBasePath()
        );
    }

    /** @test */
    function it_returns_migration_path_for_tables()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            generated_path() . '/Migrations/HierarchyCreateProjectSourceTable.php',
            $builder->getMigrationPath('project')
        );
    }

    /** @test */
    function it_returns_migration_path_for_table_fields()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            generated_path() . '/Migrations/HierarchyCreateDateFieldForProjectSourceTable.php',
            $builder->getMigrationPath('project', 'date')
        );
    }

    /** @test */
    function it_returns_the_migration_class_path()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            MigrationBuilder::MIGRATION_PATH,
            $builder->getMigrationClassPath()
        );

        $this->assertEquals(
            MigrationBuilder::MIGRATION_PATH . '\\' . 'HierarchyCreateProjectSourceTable',
            $builder->getMigrationClassPath('HierarchyCreateProjectSourceTable')
        );
    }

    /** @test */
    function it_returns_the_migration_class_path_by_key()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            MigrationBuilder::MIGRATION_PATH . '\\' . $builder->getTableMigrationName('project'),
            $builder->getMigrationClassPathByKey('project')
        );

        $this->assertEquals(
            MigrationBuilder::MIGRATION_PATH . '\\' . $builder->getTableFieldMigrationName('description', 'project'),
            $builder->getMigrationClassPathByKey('project', 'description')
        );
    }

    /** @test */
    function it_gets_the_column_type_for_given_key()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            $builder->getColumnType('textarea'),
            'text'
        );
    }

    /** @test */
    function it_returns_the_default_column_type()
    {
        $builder = $this->getBuilder();

        $this->assertEquals(
            $builder->getColumnType('nonexistingkey'),
            'string'
        );
    }

}