<?php

use Nuclear\Hierarchy\NodeField;
use Nuclear\Hierarchy\NodeType;
use Nuclear\Hierarchy\Repositories\NodeFieldRepository;

class NodeFieldsRepositoryTest extends TestBase {

    protected function getNodeType($attributes = [])
    {
        $attributes = !empty($attributes) ? $attributes : [
            'name' => 'project',
            'label' => 'Project',
            'description' => ''
        ];

        return NodeType::create($attributes);
    }

    protected function getNodeField($attributes = [])
    {
        $attributes = !empty($attributes) ? $attributes : [
            'name' => 'area',
            'label' => 'Area',
            'description' => '',
            'type' => 'text',
            'position' => 1.0
        ];

        return NodeField::create($attributes);
    }

    /** @test */
    function it_creates_a_node_field()
    {
        $builderService = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract');
        $builderService->buildField('area', 'text', 'project', ['area'])
            ->shouldBeCalled();

        $repository = new NodeFieldRepository(
            $builderService->reveal());

        $nodeType = $this->getNodeType();

        $repository->create($nodeType->getKey(), [
            'name' => 'area',
            'label' => 'Area',
            'description' => '',
            'type' => 'text',
            'position' => 1.0
        ]);
    }

    /** @test */
    function it_destroys_a_node_field()
    {
        $nodeType = $this->getNodeType();

        $builderService = $this->prophesize('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract');
        $builderService->buildField('area', 'text', 'project', ['area'])
            ->shouldBeCalled();

        $builderService->destroyField('area', 'project', [])
            ->shouldBeCalled();

        $repository = new NodeFieldRepository(
            $builderService->reveal());

        $nodeField = $repository->create($nodeType->getKey(), [
            'name' => 'area',
            'label' => 'Area',
            'description' => '',
            'type' => 'text',
            'position' => 1.0
        ]);

        $repository->destroy($nodeField->getKey());
    }

    /** @test */
    function it_returns_the_model_name()
    {
        $builderServiceMock = $this->getMockBuilder('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract')
            ->getMock();

        $repository = new NodeFieldRepository($builderServiceMock);

        $this->assertEquals(
            'Nuclear\Hierarchy\NodeField',
            $repository->getModelName()
        );
    }

    /** @test */
    function it_returns_the_type_model_name()
    {
        $builderServiceMock = $this->getMockBuilder('Nuclear\Hierarchy\Contract\Builders\BuilderServiceContract')
            ->getMock();

        $repository = new NodeFieldRepository($builderServiceMock);

        $this->assertEquals(
            'Nuclear\Hierarchy\NodeType',
            $repository->getTypeModelName()
        );
    }

}