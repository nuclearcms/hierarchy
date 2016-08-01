<?php

use Illuminate\Database\Eloquent\Collection;
use Nuclear\Hierarchy\Builders\BuilderService;

class BuilderServiceTest extends TestBase {

    /** @test */
    function it_builds_a_source_table()
    {
        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->build('project')
            ->willReturn(null)
            ->shouldBeCalled();

        $formBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\FormBuilderContract');
        $formBuilder->build('project')
            ->willReturn(null)
            ->shouldBeCalled();

        $migrationBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract');
        $migrationBuilder->buildSourceTableMigration('project')
            ->willReturn('TestMigration')
            ->shouldBeCalled();

        $service = new BuilderService(
            $modelBuilder->reveal(),
            $migrationBuilder->reveal(),
            $formBuilder->reveal()
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
        $model = $this->prophesize('Nuclear\Hierarchy\Contract\NodeTypeContract');
        $collection = new Collection();
        $model->getFields()
            ->willReturn($collection)
            ->shouldBeCalled();

        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->build('project', $collection)
            ->willReturn(null)
            ->shouldBeCalled();

        $model->getName()
            ->willReturn('project')
            ->shouldBeCalled();

        $model->getFields()
            ->willReturn($collection)
            ->shouldBeCalled();

        $formBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\FormBuilderContract');
        $formBuilder->build('project', $collection)
            ->willReturn(null)
            ->shouldBeCalled();

        $migrationBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract');
        $migrationBuilder->buildFieldMigrationForTable('description', 'text', false, 'project')
            ->willReturn('TestMigration')
            ->shouldBeCalled();

        $service = new BuilderService(
            $modelBuilder->reveal(),
            $migrationBuilder->reveal(),
            $formBuilder->reveal()
        );

        try
        {
            $service->buildField('description', 'text', false, 'project', $model->reveal());
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
    function it_builds_a_form_for_a_node_type()
    {
        $model = $this->prophesize('Nuclear\Hierarchy\Contract\NodeTypeContract');
        $model->getName()
            ->willReturn('project')
            ->shouldBeCalled();

        $collection = new Collection();
        $model->getFields()
            ->willReturn($collection)
            ->shouldBeCalled();

        $formBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\FormBuilderContract');
        $formBuilder->build('project', $collection)
            ->willReturn(null)
            ->shouldBeCalled();

        $migrationBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\MigrationBuilderContract');
        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');

        $service = new BuilderService(
            $modelBuilder->reveal(),
            $migrationBuilder->reveal(),
            $formBuilder->reveal()
        );

        $service->buildForm($model->reveal());
    }

    /** @test */
    function it_destroys_a_source_table()
    {
        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->destroy('project')
            ->willReturn(null)
            ->shouldBeCalled();

        $formBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\FormBuilderContract');
        $formBuilder->destroy('project')
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
            $formBuilder->reveal()
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
        $model = $this->prophesize('Nuclear\Hierarchy\Contract\NodeTypeContract');
        $collection = new Collection();

        $model->getFields()
            ->willReturn($collection)
            ->shouldBeCalled();

        $modelBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\ModelBuilderContract');
        $modelBuilder->build('project', $collection)
            ->willReturn(null)
            ->shouldBeCalled();

        $model->getFields()
            ->willReturn($collection)
            ->shouldBeCalled();

        $formBuilder = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\FormBuilderContract');
        $formBuilder->build('project', $collection)
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
            $formBuilder->reveal()
        );

        // At this time it is kind of impossible
        // to test if the migration did run like we do
        // in the build tests since it quits before reaching
        // the destroyFieldMigrationForTable method
        // We assume if the destroyFieldMigrationForTable is
        // called, the method reached to the end without any problem
        $service->destroyField('description', 'project', $model->reveal());
    }

}