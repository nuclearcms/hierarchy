<?php

use Nuclear\Hierarchy\NodeType;
use Nuclear\Hierarchy\Repositories\NodeTypeRepository;
use Prophecy\Argument;

class NodeTypeRepositoryTest extends TestBase {

    /** @test */
    function it_creates_a_node_type()
    {
        $builderService = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract');
        $builderService->buildTable('project', Argument::type('int'))
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
        // This part is for the sake of setting the test up
        $builderService->buildTable('project', Argument::type('int'))
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

        $builderService->destroyTable('project', [], $nodeType->getKey())
            ->shouldBeCalled();

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