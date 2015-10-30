<?php

use Nuclear\Hierarchy\NodeType;
use Nuclear\Hierarchy\Repositories\NodeTypeRepository;

class NodeTypeRepositoryTest extends TestBase {

    /** @test */
    function it_created_a_node_type()
    {
        $builderService = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract');
        $builderService->buildTable('project')
            ->shouldBeCalled();

        $repository = new NodeTypeRepository(
            $builderService->reveal());

        $nodeType = $repository->create([
            'name' => 'project',
            'label' => 'Project'
        ]);

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\NodeType',
            $nodeType);

        $this->assertEquals(
            1,
            NodeType::count()
        );
    }

    /** @test */
    function it_destroys_a_node_type()
    {
        $builderService = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract');
        $builderService->buildTable('project')
            ->shouldBeCalled();

        $builderService->destroyTable('project', [])
            ->shouldBeCalled();

        $repository = new NodeTypeRepository(
            $builderService->reveal());

        $nodeType = $repository->create([
            'name' => 'project',
            'label' => 'Project'
        ]);

        $this->assertEquals(
            1,
            NodeType::count()
        );

        $repository->destroy($nodeType->getKey());

        $this->assertEquals(
            0,
            NodeType::count()
        );
    }

    /** @test */
    function it_returns_the_model_name()
    {
        $builderServiceMock = $this->getMockBuilder('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract')
            ->getMock();

        $repository = new NodeTypeRepository($builderServiceMock);

        $this->assertEquals(
            'Nuclear\Hierarchy\NodeType',
            $repository->getModelName()
        );
    }

}