<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nuclear\Hierarchy\Node;
use Nuclear\Hierarchy\NodeSource;

class NodeTest extends TestBase {

    public function setUp()
    {
        parent::setUp();

        // Set the languages
        config()->set('translatable.locales', ['en', 'tr']);

        $this->setUpNodeType();
    }

    protected function getNode()
    {
        $node = new Node();

        $node->setNodeTypeKey(1);

        $node->save();

        return $node;
    }

    protected function setUpNodeType()
    {
        $typeRepository = $this->app->make('Nuclear\Hierarchy\Repositories\NodeTypeRepository');
        $fieldRepository = $this->app->make('Nuclear\Hierarchy\Repositories\NodeFieldRepository');

        $nodeType = $typeRepository->create([
            'name'  => 'project',
            'label' => 'Project'
        ]);

        $fieldArea = $fieldRepository->create(
            $nodeType->getKey(), [
            'name'        => 'area',
            'label'       => 'Area',
            'description' => '',
            'type'        => 'integer',
            'position'    => 0.1
        ]);

        $fieldDescription = $fieldRepository->create(
            $nodeType->getKey(), [
            'name'        => 'description',
            'label'       => 'Description',
            'description' => '',
            'type'        => 'text',
            'position'    => 0.2
        ]);
    }

    /** @test */
    function it_is_related_to_the_node_type()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\BelongsTo',
            $node->nodeType()
        );

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\NodeType',
            $node->nodeType
        );
    }

    /** @test */
    function it_sets_and_gets_node_type_key()
    {
        $node = $this->getNode();

        $this->assertEquals(
            $node->getNodeTypeKey(),
            1
        );

        $node->setNodeTypeKey(2);

        $this->assertEquals(
            $node->getNodeTypeKey(),
            2
        );
    }

    /** @test */
    function it_sets_node_type_by_key()
    {
        $node = $this->getNode();

        $node->setNodeTypeByKey(1);

        try
        {
            $node->setNodeTypeByKey(1337);
        } catch (ModelNotFoundException $e)
        {
            return;
        }

        $this->fail('Something went wrong. Test fails!');
    }

    /** @test */
    function it_checks_if_given_attribute_is_a_translation_attribute()
    {
        $node = $this->getNode();

        $this->assertTrue(
            $node->isTranslationAttribute('title')
        );

        $this->assertTrue(
            $node->isTranslationAttribute('node_name')
        );

        $this->assertTrue(
            $node->isTranslationAttribute('area')
        );

        $this->assertTrue(
            $node->isTranslationAttribute('description')
        );

        $this->assertFalse(
            $node->isTranslationAttribute('non-existing-key')
        );

        $this->assertFalse(
            $node->isTranslationAttribute('children_order')
        );

        $this->assertFalse(
            $node->isTranslationAttribute('locked')
        );

        $this->assertFalse(
            $node->isTranslationAttribute('status')
        );
    }

    /** @test */
    function it_checks_if_an_attribute_is_fillable()
    {
        $node = $this->getNode();

        $this->assertTrue(
            $node->isFillable('title')
        );

        $this->assertTrue(
            $node->isFillable('node_name')
        );

        $this->assertTrue(
            $node->isFillable('area')
        );

        $this->assertTrue(
            $node->isFillable('description')
        );

        $this->assertFalse(
            $node->isFillable('non-existing-key')
        );

        $this->assertFalse(
            $node->isFillable('lft')
        );

        $this->assertFalse(
            $node->isFillable('depth')
        );
    }

    /** @test */
    function it_creates_a_new_node_source()
    {
        $node = $this->getNode();

        $nodeSource = $node->getNewTranslation('tr');

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\NodeSource',
            $nodeSource
        );

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\NodeSource',
            $node->translations->find($nodeSource->getKey())
        );
    }

    /** @test */
    function it_gets_children()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $node->getChildren()
        );
    }

    /** @test */
    function it_gets_ordered_children()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $node->getOrderedChildren()
        );

        $this->assertInstanceOf(
            'Illuminate\Pagination\LengthAwarePaginator',
            $node->getOrderedChildren(15)
        );
    }

    /** @test */
    function it_gets_position_ordered_children()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $node->getPositionOrderedChildren()
        );

        $this->assertInstanceOf(
            'Illuminate\Pagination\LengthAwarePaginator',
            $node->getPositionOrderedChildren(15)
        );
    }

    /** @test */
    function it_gets_published_ordered_children()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $node->getPublishedOrderedChildren()
        );

        $this->assertInstanceOf(
            'Illuminate\Pagination\LengthAwarePaginator',
            $node->getPublishedOrderedChildren(15)
        );
    }

    /** @test */
    function it_gets_published_position_ordered_children()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $node->getPublishedPositionOrderedChildren()
        );

        $this->assertInstanceOf(
            'Illuminate\Pagination\LengthAwarePaginator',
            $node->getPublishedPositionOrderedChildren(15)
        );
    }

    /** @test */
    function it_checks_translated_children()
    {
        $node = $this->getNode();

        $this->assertFalse(
            $node->hasTranslatedChildren('en')
        );
    }

    /** @test */
    function it_checks_if_hides_children()
    {
        $node = $this->getNode();

        $this->assertFalse(
            $node->hidesChildren()
        );
    }

    /** @test */
    function it_sets_and_gets_base_attributes()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->visible,
            1
        );

        $this->assertNull(
            $node->getAttribute('visible'),
            1
        );

        $node->visible = 0;

        $this->assertEquals(
            $node->visible,
            0
        );

        $node->setAttribute('visible', 1);

        $this->assertEquals(
            $node->getAttribute('visible'),
            1
        );
    }

    /** @test */
    function it_sets_and_gets_node_source_base_attributes_for_default_locale()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->title
        );

        $this->assertNull(
            $node->getAttribute('title')
        );

        $node->title = 'Test title';

        $this->assertEquals(
            $node->title,
            'Test title'
        );

        $node->setAttribute('title', 'Another test title');

        $this->assertEquals(
            $node->getAttribute('title'),
            'Another test title'
        );
    }

    /** @test */
    function it_sets_and_gets_node_source_extension_attributes_for_default_locale()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->description
        );

        $this->assertNull(
            $node->getAttribute('description')
        );

        $node->description = 'Test description';

        $this->assertEquals(
            $node->description,
            'Test description'
        );

        $node->setAttribute('description', 'Another test description');

        $this->assertEquals(
            $node->getAttribute('description'),
            'Another test description'
        );
    }

    /** @test */
    function it_sets_and_gets_node_source_base_attributes_for_different_locale()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->{'title:tr'}
        );

        $this->assertNull(
            $node->getAttribute('title:tr')
        );

        $node->{'title:tr'} = 'Türkçe Test Başlığı';

        $this->assertEquals(
            $node->{'title:tr'},
            'Türkçe Test Başlığı'
        );

        $node->setAttribute('title:tr', 'Başka Türkçe Test Başlığı');

        $this->assertEquals(
            $node->getAttribute('title:tr'),
            'Başka Türkçe Test Başlığı'
        );

        $node->translate('tr')->title = 'Bambaşka Türkçe Test Başlığı';

        $this->assertEquals(
            $node->translate('tr')->title,
            'Bambaşka Türkçe Test Başlığı'
        );
    }

    /** @test */
    function it_sets_and_gets_node_source_extension_attributes_for_different_locale()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->{'description:tr'}
        );

        $this->assertNull(
            $node->getAttribute('description:tr')
        );

        $node->{'description:tr'} = 'Türkçe Test Açıklaması';

        $this->assertEquals(
            $node->{'description:tr'},
            'Türkçe Test Açıklaması'
        );

        $node->setAttribute('description:tr', 'Başka Türkçe Test Açıklaması');

        $this->assertEquals(
            $node->getAttribute('description:tr'),
            'Başka Türkçe Test Açıklaması'
        );

        $node->translate('tr')->description = 'Bambaşka Türkçe Test Açıklaması';

        $this->assertEquals(
            $node->translate('tr')->description,
            'Bambaşka Türkçe Test Açıklaması'
        );
    }

    /** @test */
    function it_fills_given_params()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->visible
        );

        $this->assertNull($node->title);
        $this->assertNull($node->{'title:tr'});

        $this->assertNull($node->description);
        $this->assertNull($node->{'description:tr'});

        $node->fill([
            'visible' => 0,
            'en'      => [
                'title'       => 'English Title',
                'description' => 'English Description'
            ],
            'tr'      => [
                'title'       => 'Türkçe Başlık',
                'description' => 'Türkçe Açıklama'
            ]
        ]);

        $this->assertEquals(
            $node->visible,
            0
        );

        $this->assertEquals($node->title, 'English Title');
        $this->assertEquals($node->{'title:tr'}, 'Türkçe Başlık');

        $this->assertEquals($node->description, 'English Description');
        $this->assertEquals($node->{'description:tr'}, 'Türkçe Açıklama');
    }

    /** @test */
    function it_saves_with_dirty_translations_on_create()
    {
        $node = $this->getNode();

        $node->fill([
            'visible' => 0,
            'en'      => [
                'title'       => 'English Title',
                'description' => 'English Description'
            ],
            'tr'      => [
                'title'       => 'Türkçe Başlık',
                'description' => 'Türkçe Açıklama'
            ]
        ]);

        $this->assertCount(
            2,
            $node->translations
        );

        $this->assertCount(
            0,
            NodeSource::all()
        );

        $this->assertTrue(
            $node->save()
        );

        $node->load('translations');

        $this->assertCount(
            2,
            $node->translations
        );

        $this->assertCount(
            2,
            NodeSource::all()
        );
    }

}