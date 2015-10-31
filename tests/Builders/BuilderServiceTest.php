<?php

use Nuclear\Hierarchy\Builders\BuilderService;

class BuilderServiceTest extends TestBase {

    /** @test */
    function it_builds_a_source_table()
    {
        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->build('project', [])
            ->willReturn(null)
            ->shouldBeCalled();

        $cacheBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract');
        $cacheBuilder->build(1, [])
            ->willReturn(null)
            ->shouldBeCalled();

        $migrationBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract');
        $migrationBuilder->buildSourceTableMigration('project')
            ->willReturn('TestMigration')
            ->shouldBeCalled();

        $service = new BuilderService(
            $modelBuilder->reveal(),
            $migrationBuilder->reveal(),
            $cacheBuilder->reveal()
        );

        try
        {
            $service->buildTable('project', 1);
        } catch(\Exception $e)
        {
            if($e->getMessage() === 'up')
            {
                return;
            }
        }

        $this->fail('The migration did not run');
    }

    /** @test */
    function it_builds_a_field_for_a_source_table()
    {
        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->build('project', ['description'])
            ->willReturn(null)
            ->shouldBeCalled();

        $model = $this->prophesize('Nuclear\Hierarchy\Contract\NodeTypeContract');
        $model->getKey()
            ->willReturn(1)
            ->shouldBeCalled();

        $cacheBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract');
        $cacheBuilder->build(1, ['description'])
            ->willReturn(null)
            ->shouldBeCalled();

        $migrationBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract');
        $migrationBuilder->buildFieldMigrationForTable('description', 'text', 'project')
            ->willReturn('TestMigration')
            ->shouldBeCalled();

        $service = new BuilderService(
            $modelBuilder->reveal(),
            $migrationBuilder->reveal(),
            $cacheBuilder->reveal()
        );

        try
        {
            $service->buildField('description', 'text', 'project', ['description'], $model->reveal());
        } catch(\Exception $e)
        {
            if($e->getMessage() === 'up')
            {
                return;
            }

            throw $e;
        }

        $this->fail('The migration did not run');
    }

    /** @test */
    function it_destroys_a_source_table()
    {
        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->destroy('project')
            ->willReturn(null)
            ->shouldBeCalled();

        $cacheBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract');
        $cacheBuilder->destroy(1)
            ->willReturn(null)
            ->shouldBeCalled();

        $migrationBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract');
        $migrationBuilder->getMigrationClassPathByKey('project')
            ->willReturn('TestMigration')
            ->shouldBeCalled();

        $migrationBuilder->destroySourceTableMigration('project', [])
            ->shouldBeCalled();

        $service = new BuilderService(
            $modelBuilder->reveal(),
            $migrationBuilder->reveal(),
            $cacheBuilder->reveal()
        );

        // At this time it is kind of impossible
        // to test if the migration did run like we do
        // in the build tests since it quits before reaching
        // the destroySourceTableMigration method
        // We assume if the destroySourceTableMigration is
        // called, the method reached to the end without any problem
        $service->destroyTable('project', [], 1);
    }

    /** @test */
    function it_destroys_a_field_from_a_source_table()
    {
        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->build('project', [])
            ->willReturn(null)
            ->shouldBeCalled();

        $model = $this->prophesize('Nuclear\Hierarchy\Contract\NodeTypeContract');
        $model->getKey()
            ->willReturn(1)
            ->shouldBeCalled();

        $cacheBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\CacheBuilderContract');
        $cacheBuilder->build(1, [])
            ->willReturn(null)
            ->shouldBeCalled();

        $migrationBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract');
        $migrationBuilder->getMigrationClassPathByKey('project', 'description')
            ->willReturn('TestMigration')
            ->shouldBeCalled();

        $migrationBuilder->destroyFieldMigrationForTable('description', 'project')
            ->shouldBeCalled();

        $service = new BuilderService(
            $modelBuilder->reveal(),
            $migrationBuilder->reveal(),
            $cacheBuilder->reveal()
        );

        // At this time it is kind of impossible
        // to test if the migration did run like we do
        // in the build tests since it quits before reaching
        // the destroyFieldMigrationForTable method
        // We assume if the destroyFieldMigrationForTable is
        // called, the method reached to the end without any problem
        $service->destroyField('description', 'project', [], $model->reveal());
    }

}