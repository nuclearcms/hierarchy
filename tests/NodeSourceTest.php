<?php

use Baum\Node;
use Nuclear\Hierarchy\NodeSource;

class NodeSourceTest extends TestBase {

    public function setUp()
    {
        parent::setUp();

        $this->setUpNodeType();

        NodeSource::flushEventListeners();
        NodeSource::boot();
    }

    protected function getNodeSource($locale = 'en', $type = 'project')
    {
        return NodeSource::newWithType($locale, $type);
    }

    protected function populateNodeSource($nodeSource)
    {
        $nodeSource->title = 'Node Title';
        $nodeSource->setNodeNameFromTitle();

        return $nodeSource;
    }

    protected function setUpNodeType()
    {
        $typeRepository = $this->app->make('Nuclear\Hierarchy\Repositories\NodeTypeRepository');
        $fieldRepository = $this->app->make('Nuclear\Hierarchy\Repositories\NodeFieldRepository');

        $nodeType = $typeRepository->create([
            'name' => 'project',
            'label' => 'Project'
        ]);

        $fieldArea = $fieldRepository->create(
            $nodeType->getKey(), [
                'name' => 'area',
                'label' => 'Area',
                'description' => '',
                'type' => 'integer',
                'position' => 0.1
        ]);

        $fieldDescription = $fieldRepository->create(
            $nodeType->getKey(), [
            'name' => 'description',
            'label' => 'Description',
            'description' => '',
            'type' => 'text',
            'position' => 0.2
        ]);
    }

    /** @test */
    function it_is_related_to_parent_node()
    {
        $nodeSource = $this->getNodeSource();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\BelongsTo',
            $nodeSource->node()
        );
    }

    /** @test */
    function it_is_related_to_its_source()
    {
        $nodeSource = $this->getNodeSource();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasOne',
            $nodeSource->source()
        );
    }

    /** @test */
    function it_gets_the_source_model_name()
    {
        $nodeSource = $this->getNodeSource();

        $this->assertEquals(
            $nodeSource->getSourceModelName(),
            'gen\\Entities\\NsProject'
        );

        $this->assertEquals(
            $nodeSource->getSourceModelName('category'),
            'gen\\Entities\\NsCategory'
        );
    }

    /** @test */
    function it_saves_and_gets_source_model()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);

        $this->assertInstanceOf(
            'gen\\Entities\\' . source_model_name($nodeSource->source_type),
            $nodeSource->getSource()
        );
        $this->assertNull(
            $nodeSource->source
        );

        // We check this because the source model should be saved to
        // the relation after the first save and the model retrieves
        // source from relation if the model exists in the database.
        $nodeSource->save();

        $this->assertInstanceOf(
            'gen\\Entities\\' . source_model_name($nodeSource->source_type),
            $nodeSource->getSource()
        );

        // We should now be able to access the source through the
        // relation magic property.
        $this->assertInstanceOf(
            'gen\\Entities\\' . source_model_name($nodeSource->source_type),
            $nodeSource->source
        );

        // Now we are attempting to save an existing relation model
        $nodeSource->save();

        $this->assertInstanceOf(
            'gen\\Entities\\' . source_model_name($nodeSource->source_type),
            $nodeSource->getSource()
        );

        // We should now be able to access the source through the
        // relation magic property.
        $this->assertInstanceOf(
            'gen\\Entities\\' . source_model_name($nodeSource->source_type),
            $nodeSource->source
        );
    }

    /** @test */
    function it_gets_and_sets_self_attributes_before_created()
    {
        $nodeSource = $this->getNodeSource();

        $nodeSource->title = 'Another Title';

        $this->assertEquals(
            $nodeSource->getAttribute('title'),
            'Another Title'
        );

        $nodeSource->setAttribute('title', 'Yet Another Title');

        $this->assertEquals(
            $nodeSource->title,
            'Yet Another Title'
        );
    }

    /** @test */
    function it_gets_and_sets_source_attributes_before_created()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);

        $nodeSource->area = 20000;

        $this->assertEquals(
            $nodeSource->getAttribute('area'),
            20000
        );

        $nodeSource->setAttribute('area', 30000);

        $this->assertEquals(
            $nodeSource->area,
            30000
        );
    }

    /** @test */
    function it_gets_and_sets_self_attributes_after_created()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);

        $nodeSource->save();

        $this->assertEquals(
            $nodeSource->getAttribute('title'),
            'Node Title'
        );

        $nodeSource->title = 'Another Title';

        $this->assertEquals(
            $nodeSource->getAttribute('title'),
            'Another Title'
        );

        $nodeSource->setAttribute('title', 'Yet Another Title');

        $this->assertEquals(
            $nodeSource->title,
            'Yet Another Title'
        );

        $nodeSource->save();

        // Find and check again
        $nodeSource = NodeSource::find($nodeSource->getKey());

        $this->assertEquals(
            $nodeSource->title,
            'Yet Another Title'
        );
    }

    /** @test */
    function it_gets_and_sets_source_attributes_after_created()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);
        $nodeSource->area = 10000;

        $nodeSource->save();

        $this->assertEquals(
            $nodeSource->getAttribute('area'),
            10000
        );

        $nodeSource->area = 20000;

        $this->assertEquals(
            $nodeSource->getAttribute('area'),
            20000
        );

        $nodeSource->setAttribute('area', 30000);

        $this->assertEquals(
            $nodeSource->area,
            30000
        );

        $nodeSource->save();

        // Find and check again
        $nodeSource = NodeSource::find($nodeSource->getKey());

        $this->assertEquals(
            $nodeSource->area,
            30000
        );
    }

    /** @test */
    function it_checks_if_base_is_dirty_before_created()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);

        $this->assertTrue(
            $nodeSource->isDirty()
        );
    }

    /** @test */
    function it_checks_if_base_is_dirty_after_created()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);

        $this->assertTrue(
            $nodeSource->isDirty()
        );

        $nodeSource->save();

        $this->assertFalse(
            $nodeSource->isDirty()
        );

        // Find and check again
        $nodeSource = NodeSource::find($nodeSource->getKey());

        $this->assertFalse(
            $nodeSource->isDirty()
        );
    }

    /** @test */
    function it_checks_if_source_is_dirty_after_created()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);
        $nodeSource->area = 10000;

        $nodeSource->save();

        $this->assertFalse(
            $nodeSource->isDirty()
        );

        $nodeSource->area = 20000;

        $this->assertTrue(
            $nodeSource->isDirty()
        );

        $nodeSource->save();

        $this->assertFalse(
            $nodeSource->isDirty()
        );

        $nodeSource->title = 'Different Title';

        $this->assertTrue(
            $nodeSource->isDirty()
        );

        $nodeSource->save();

        $this->assertFalse(
            $nodeSource->isDirty()
        );

        // Find and check again
        $nodeSource = NodeSource::find($nodeSource->getKey());

        $this->assertFalse(
            $nodeSource->isDirty()
        );

        $nodeSource->area = 30000;

        $this->assertTrue(
            $nodeSource->isDirty()
        );

        $nodeSource->save();

        $this->assertFalse(
            $nodeSource->isDirty()
        );
    }

    /** @test */
    function it_gets_title()
    {
        $nodeSource = $this->getNodeSource();
        $nodeSource->title = 'Node Title';

        $this->assertEquals(
            $nodeSource->getTitle(),
            'Node Title'
        );
    }

    /** @test */
    function it_gets_node_name()
    {
        $nodeSource = $this->getNodeSource();
        $nodeSource->node_name = 'node-title';

        $this->assertEquals(
            $nodeSource->getNodeName(),
            'node-title'
        );
    }

    /** @test */
    function it_sets_and_mutates_node_name()
    {
        $nodeSource = $this->getNodeSource();

        $nodeSource->setNodeName('Some other node name ğüiçş');

        $this->assertEquals(
            $nodeSource->getNodeName(),
            'some-other-node-name-gueics'
        );
    }

    /** @test */
    function it_sets_node_name_from_title()
    {
        $nodeSource = $this->getNodeSource();

        $nodeSource->title = 'Node Title';
        $nodeSource->setNodeNameFromTitle();

        $this->assertEquals(
            $nodeSource->getNodeName(),
            'node-title'
        );
    }

    /** @test */
    function it_auto_sets_node_name_when_saving()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);

        $nodeSource->save();

        $this->assertEquals(
            $nodeSource->getNodeName(),
            'node-title'
        );

        $nodeSource->title = 'Another title';
        $nodeSource->save();

        $this->assertEquals(
            $nodeSource->getNodeName(),
            'node-title'
        );

        $nodeSource->node_name = '';
        $nodeSource->save();

        $this->assertEquals(
            $nodeSource->getNodeName(),
            'another-title'
        );
    }

    /** @test */
    function it_converts_model_to_array()
    {
        $nodeSource = $this->getNodeSource();
        $this->populateNodeSource($nodeSource);

        $nodeSource->save();

        $this->assertArrayHasKey(
            'title',
            $nodeSource->toArray()
        );

        $this->assertArrayHasKey(
            'description',
            $nodeSource->toArray()
        );
    }

    /** @test */
    function it_gets_a_new_source_model()
    {
        $nodeSource = $this->getNodeSource();

        $this->assertInstanceOf(
            'gen\\Entities\\NsProject',
            $nodeSource->getNewSourceModel('project')
        );
    }

}