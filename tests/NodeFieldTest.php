<?php

use Nuclear\Hierarchy\NodeField;

class NodeFieldTest extends TestBase {

    protected function getNodeField($attributes = [])
    {
        $attributes = !empty($attributes) ? $attributes : [
            'name' => 'area',
            'label' => 'Area',
            'description' => '',
            'type' => 'text',
            'position' => 1.0,
            'search_priority' => 0
        ];

        return NodeField::create($attributes);
    }

    /** @test */
    function it_is_related_to_parent_node_type()
    {
        $nodeField = $this->getNodeField();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\BelongsTo',
            $nodeField->nodeType()
        );
    }

    /** @test */
    function it_gets_the_node_field_name()
    {
        $nodeField = $this->getNodeField();

        $this->assertEquals(
            'area',
            $nodeField->getName()
        );
    }

    /** @test */
    function it_gets_the_node_field_type()
    {
        $nodeField = $this->getNodeField();

        $this->assertEquals(
            'text',
            $nodeField->getType()
        );
    }

    /** @test */
    function it_checks_if_the_node_field_is_indexed()
    {
        $nodeField = $this->getNodeField();

        $this->assertFalse(
            $nodeField->isIndexed()
        );

        $nodeField->indexed = 1;

        $this->assertTrue(
            $nodeField->isIndexed()
        );
    }

    /** @test */
    function it_checks_if_the_node_field_is_visible()
    {
        $nodeField = $this->getNodeField();

        $this->assertFalse(
            $nodeField->isVisible()
        );

        $nodeField->visible = 1;

        $this->assertTrue(
            $nodeField->isVisible()
        );
    }

}