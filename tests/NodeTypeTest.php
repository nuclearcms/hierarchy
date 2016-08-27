<?php

use Nuclear\Hierarchy\NodeType;

class NodeTypeTest extends TestBase {

    protected function getNodeType($attributes = [])
    {
        $attributes = !empty($attributes) ? $attributes : [
            'name' => 'project',
            'label' => 'Project',
            'description' => ''
        ];

        return NodeType::create($attributes);
    }

    /** @test */
    function it_is_related_to_child_nodes()
    {
        $nodeType = $this->getNodeType();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasMany',
            $nodeType->nodes()
        );
    }

    /** @test */
    function it_is_related_to_child_fields()
    {
        $nodeType = $this->getNodeType();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasMany',
            $nodeType->fields()
        );
    }

    /** @test */
    function it_gets_the_node_type_name()
    {
        $nodeType = $this->getNodeType([
            'name' => 'project',
            'label' => 'Project'
        ]);

        $this->assertEquals(
            'project',
            $nodeType->getName()
        );
    }

    /** @test */
    function it_gets_child_fields()
    {
        $nodeType = $this->getNodeType();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $nodeType->getFields()
        );
    }

    /** @test */
    function it_adds_a_field()
    {
        $nodeType = $this->getNodeType();

        $this->assertCount(
            0,
            $nodeType->getFields()
        );

        $field = $nodeType->addField([
            'name' => 'area',
            'label' => 'Area',
            'type' => 'integer',
            'position' => 1,
            'description' => '',
            'search_priority' => 0
        ]);

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\NodeField',
            $field
        );

        $fields = $nodeType->getFields();

        $this->assertCount(1, $fields);
    }

    /** @test */
    function it_gets_the_fields_ordered()
    {
        $nodeType = $this->getNodeType();

        $this->assertCount(
            0,
            $nodeType->getFields()
        );

        $nodeType->addField([
            'name' => 'area',
            'label' => 'Area',
            'type' => 'integer',
            'position' => 2,
            'description' => '',
            'search_priority' => 0
        ]);

        $nodeType->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'text',
            'position' => 1,
            'description' => '',
            'search_priority' => 10
        ]);

        $fields = $nodeType->getFields();

        $this->assertCount(2, $fields);

        $this->assertEquals(
            $fields->first()->position,
            "1.0"
        );
    }

    /** @test */
    function it_gets_the_field_keys()
    {
        $nodeType = $this->getNodeType();

        $this->assertCount(
            0,
            $nodeType->getFields()
        );

        $nodeType->addField([
            'name' => 'area',
            'label' => 'Area',
            'type' => 'integer',
            'position' => 2,
            'description' => '',
            'search_priority' => 0
        ]);

        $nodeType->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'text',
            'position' => 1,
            'description' => '',
            'search_priority' => 10
        ]);

        $fields = $nodeType->getFields();

        $this->assertCount(2, $fields);

        $this->assertEquals(
            ['description', 'area'],
            $nodeType->getFieldKeys()
        );
    }

    /** @test */
    function it_checks_if_node_type_is_mailing()
    {
        $nodeType = $this->getNodeType();

        $this->assertFalse($nodeType->isTypeMailing());

        $nodeType->mailing = 1;

        $this->assertTrue($nodeType->isTypeMailing());
    }

    /** @test */
    function it_checks_if_node_type_is_taggable()
    {
        $nodeType = $this->getNodeType();

        $this->assertFalse($nodeType->isTaggable());

        $nodeType->taggable = 1;
        
        $this->assertTrue($nodeType->isTaggable());
    }

}