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
            'position'    => 0.1,
            'search_priority' => 0
        ]);

        $fieldDescription = $fieldRepository->create(
            $nodeType->getKey(), [
            'name'        => 'description',
            'label'       => 'Description',
            'description' => '',
            'type'        => 'text',
            'position'    => 0.2,
            'search_priority' => 10
        ]);

        $nodeType = $typeRepository->create([
            'name'  => 'category',
            'label' => 'Category'
        ]);

        $fieldDescription = $fieldRepository->create(
            $nodeType->getKey(), [
            'name'        => 'description',
            'label'       => 'Description',
            'description' => '',
            'type'        => 'text',
            'position'    => 0.2,
            'search_priority' => 0
        ]);

        $fieldContent = $fieldRepository->create(
            $nodeType->getKey(), [
            'name'        => 'content',
            'label'       => 'Content',
            'description' => '',
            'type'        => 'text',
            'position'    => 0.3,
            'search_priority' => 0
        ]);
    }

    /** @test */
    function it_sets_published_at_date()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Carbon\Carbon',
            $node->published_at
        );

        $node->save();

        $this->assertRegExp(
            '/^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1‌​1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]‌​[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0‌​2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02‌​)(-)(29)))(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))$/',
            $node->published_at->toDateTimeString()
        );
    }

    /** @test */
    function it_fires_a_node_event()
    {
        $node = $this->getNode();
        $id = $node->getKey();

        $dispatcher = $this->app->make('Illuminate\Contracts\Events\Dispatcher');

        $dispatcher->listen('project.saved', function ($project) use ($id)
        {
            if ($project->getKey() === $id)
            {
                throw new Exception('Project saved.');
            }
        });

        try
        {
            $node->fireNodeEvent('saved');
        } catch (Exception $e)
        {
            if ($e->getMessage() === 'Project saved.')
            {
                return;
            }
        }

        $this->fail('Event not fired, test fails');
    }

    /** @test */
    function it_sets_published_date_if_it_is_empty_on_creation()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Carbon\Carbon',
            $node->published_at
        );

        $node = new Node();

        $node->setNodeTypeKey(1);

        $yesterday = Carbon\Carbon::yesterday();

        $node->published_at = $yesterday;

        $node->save();

        $this->assertEquals(
            $node->published_at->timestamp,
            $yesterday->timestamp
        );
    }

    /** @test */
    function it_fires_node_creating_events_automatically()
    {
        $dispatcher = $this->app->make('Illuminate\Contracts\Events\Dispatcher');

        $dispatcher->listen('project.creating', function ($project)
        {
            throw new Exception('Creating project.');
        });

        try
        {
            $node = $this->getNode();
        } catch (Exception $e)
        {
            if ($e->getMessage() === 'Creating project.')
            {
                return;
            }
        }

        $this->fail('Event not fired, test fails');
    }

    /** @test */
    function it_fires_node_events_automatically()
    {
        $dispatcher = $this->app->make('Illuminate\Contracts\Events\Dispatcher');

        $node = $this->getNode();
        $id = $node->getKey();

        $dispatcher->listen('project.saved', function ($project) use ($id)
        {
            if ($project->getKey() === $id)
            {
                throw new Exception('Project saved.');
            }
        });

        try
        {
            $node->save();
        } catch (Exception $e)
        {
            if ($e->getMessage() === 'Project saved.')
            {
                return;
            }
        }

        $this->fail('Event not fired, test fails');
    }

    /** @test */
    function it_is_related_to_node_source_extensions()
    {
        $node = $this->getNode();

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasMany',
            $node->nodeSourceExtensions()
        );
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
            $node->getNodeType()
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
    function it_gets_node_type_name()
    {
        $node = $this->getNode();

        $this->assertEquals(
            $node->getNodeTypeKey(),
            1
        );

        $this->assertEquals(
            'project',
            $node->getNodeTypeName()
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

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasMany',
            $node->getOrderedChildren(false)
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

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasMany',
            $node->getPublishedOrderedChildren(false)
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

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasMany',
            $node->getPositionOrderedChildren(false)
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

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\HasMany',
            $node->getPublishedPositionOrderedChildren(false)
        );
    }

    /** @test */
    function it_gets_locale_for_node_name()
    {
        $node = $this->getNode();
        $node->{'node_name:en'} = 'about';
        $node->{'node_name:tr'} = 'hakkinda';

        $this->assertEquals(
            $node->getLocaleForNodeName('about'),
            'en'
        );

        $this->assertEquals(
            $node->getLocaleForNodeName('hakkinda'),
            'tr'
        );
    }

    /** @test */
    function it_gets_translated_attributes_with_fallback()
    {
        $node = $this->getNode();
        $node->{'node_name:en'} = 'about';
        $node->{'node_name:tr'} = '';

        $this->assertEquals(
            $node->getTranslationAttribute('node_name'),
            'about'
        );

        $this->assertEquals(
            $node->getTranslationAttribute('node_name', 'tr'),
            'about'
        );

        $this->assertEquals(
            $node->getTranslationAttribute('node_name', 'tr', false),
            ''
        );

        $this->assertNull(
            $node->getTranslationAttribute('created_at', 'tr', false)
        );

        app()->setLocale('tr');

        $this->assertEquals(
            $node->getTranslationAttribute('node_name'),
            'about'
        );

        $node->{'node_name:tr'} = 'hakkinda';

        $this->assertEquals(
            $node->getTranslationAttribute('node_name'),
            'hakkinda'
        );
    }

    /** @test */
    function it_falls_back_to_first_translation_if_default_does_not_exist()
    {
        $node = $this->getNode();
        $node->{'node_name:tr'} = 'about';

        $this->assertNull(
            $node->translate('en')
        );

        $this->assertNull(
            $node->translate(null)
        );

        $this->assertEquals(
            'about',
            $node->translateOrFirst('en')->node_name
        );

        $this->assertEquals(
            'about',
            $node->translateOrFirst(null)->node_name
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
    function it_deletes_a_translation()
    {
        $node = $this->getNode();
        $node->title = 'Test';
        $node->save();

        $this->assertTrue(
            $node->hasTranslation('en')
        );

        $this->assertFalse(
            $node->hasTranslation('tr')
        );

        $this->assertFalse(
            $node->deleteTranslation('tr')
        );

        $this->assertTrue(
            $node->deleteTranslation('en')
        );

        $this->assertFalse(
            $node->hasTranslation('en')
        );
    }

    /** @test */
    function it_publishes_the_node()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->status
        );

        $node->publish();

        $this->assertEquals(
            $node->status,
            Node::PUBLISHED
        );
    }

    /** @test */
    function it_unpublishes_the_node()
    {
        $node = $this->getNode();

        $node->status = Node::PUBLISHED;

        $this->assertEquals(
            $node->status,
            Node::PUBLISHED
        );

        $node->unpublish();

        $this->assertEquals(
            $node->status,
            Node::DRAFT
        );
    }

    /** @test */
    function it_locks_the_node()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->locked
        );

        $node->lock();

        $this->assertEquals(
            $node->locked,
            1
        );
    }

    /** @test */
    function it_unlocks_the_node()
    {
        $node = $this->getNode();

        $node->locked = 1;

        $this->assertEquals(
            $node->locked,
            1
        );

        $node->unlock();

        $this->assertEquals(
            $node->locked,
            0
        );
    }

    /** @test */
    function it_hides_the_node()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->visible
        );

        $node->hide();

        $this->assertEquals(
            $node->visible,
            0
        );
    }

    /** @test */
    function it_shows_the_node()
    {
        $node = $this->getNode();

        $node->visible = 0;

        $this->assertEquals(
            $node->visible,
            0
        );

        $node->show();

        $this->assertEquals(
            $node->visible,
            1
        );
    }

    /** @test */
    function it_archives_the_node()
    {
        $node = $this->getNode();

        $this->assertNull(
            $node->status
        );

        $node->archive();

        $this->assertEquals(
            $node->status,
            Node::ARCHIVED
        );
    }

    /** @test */
    function it_checks_if_hides_children()
    {
        $node = $this->getNode();

        $this->assertFalse(
            $node->hidesChildren()
        );

        $node->hides_children = 1;
        $node->save();

        $this->assertTrue(
            $node->hidesChildren()
        );
    }

    /** @test */
    function it_checks_if_it_can_have_children()
    {
        $node = $this->getNode();

        $this->assertTrue(
            $node->canHaveChildren()
        );

        $node->sterile = 1;

        $this->assertFalse(
            $node->canHaveChildren()
        );
    }

    /** @test */
    function it_checks_if_node_is_published()
    {
        $node = $this->getNode();

        $this->assertFalse(
            $node->isPublished()
        );

        $node->publish()->save();

        $this->assertTrue(
            $node->isPublished()
        );
    }

    /** @test */
    function it_checks_if_node_is_locked()
    {
        $node = $this->getNode();

        $this->assertFalse(
            $node->isLocked()
        );

        $node->lock()->save();

        $this->assertTrue(
            $node->isLocked()
        );
    }

    /** @test */
    function it_checks_if_node_is_visible()
    {
        $node = $this->getNode();

        $this->assertFalse(
            $node->isVisible()
        );

        $node->show()->save();

        $this->assertTrue(
            $node->isVisible()
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

    /** @test */
    function it_fails_to_transform_to_unexisting_type()
    {
        $node = $this->getNode();

        try
        {
            $node->transformInto(1337);
        } catch (\RuntimeException $e)
        {
            return;
        }

        $this->fail('Exception was not thrown. Test fails.');
    }

    /** @test */
    function it_transforms_the_node_type()
    {
        $node = $this->getNode();

        $node->fill([
            'en' => [
                'title'       => 'English Title',
                'description' => 'English Description',
                'area'        => 100000
            ],
            'tr' => [
                'title'       => 'Türkçe Başlık',
                'description' => 'Türkçe Açıklama',
                'area'        => 30000
            ]
        ]);

        $node->save();

        $this->assertEquals(
            'English Title',
            $node->title
        );

        $this->assertEquals(
            'English Description',
            $node->description
        );

        $this->assertEquals(
            100000,
            $node->area
        );

        $this->assertNull(
            $node->content
        );

        $this->assertEquals(
            'Türkçe Açıklama',
            $node->translate('tr')->description
        );

        $node->transformInto(2);

        $this->assertEquals(
            'English Title',
            $node->title
        );

        $this->assertEquals(
            'English Description',
            $node->description
        );

        $this->assertNull(
            $node->area
        );

        $this->assertEquals(
            'Türkçe Açıklama',
            $node->translate('tr')->description
        );

        $this->assertNull(
            $node->content
        );

        $node->content = 'Content';

        $node->save();

        $this->assertEquals(
            'Content',
            $node->content
        );
    }

    /** @test */
    function it_scopes_nodes_with_name()
    {
        $this->assertNull(
            Node::withName('english-title')->first()
        );

        $node = $this->getNode();

        $node->fill([
            'en' => [
                'title'       => 'English Title',
                'description' => 'English Description',
                'area'        => 100000
            ],
            'tr' => [
                'title'       => 'Türkçe Başlık',
                'description' => 'Türkçe Açıklama',
                'area'        => 30000
            ]
        ]);

        $node->save();

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Node',
            Node::withName('english-title')->first()
        );

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Node',
            Node::withName('english-title', 'en')->first()
        );

        $this->assertNull(
            Node::withName('english-title', 'tr')->first()
        );

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Node',
            Node::withName('tuerkce-baslik')->first()
        );

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Node',
            Node::withName('tuerkce-baslik', 'tr')->first()
        );
    }

    /** @test */
    function it_scopes_nodes_with_type()
    {
        $this->assertNull(
            Node::withType('project')->first()
        );

        $this->assertNull(
            Node::withType('non-existing')->first()
        );

        $node = $this->getNode();

        $node->fill([
            'en' => [
                'title'       => 'English Title',
                'description' => 'English Description',
                'area'        => 100000
            ],
            'tr' => [
                'title'       => 'Türkçe Başlık',
                'description' => 'Türkçe Açıklama',
                'area'        => 30000
            ]
        ]);

        $node->save();

        $this->assertInstanceOf(
            'Nuclear\Hierarchy\Node',
            Node::withType('project')->first()
        );

        $this->assertNull(
            Node::withType('non-existing')->first()
        );
    }

    /** @test */
    function it_makes_the_default_edit_link()
    {
        // Not possible to test this without registering routes
    }

    /** @test */
    function it_checks_if_node_is_a_newsletter()
    {
        $node = $this->getNode();

        $this->assertFalse($node->isNewsletter());
    }

    /** @test */
    function it_gets_searchable_property()
    {
        $node = $this->getNode();

        $this->assertEquals(
            $node->getSearchable(),
            [
                'columns' => [
                    'node_sources.title'         => 50,
                    'node_sources.meta_keywords' => 20,
                    'ns_projects.description'    => 10
                ],
                'joins'   => [
                    'node_sources' => ['nodes.id', 'node_sources.node_id'],
                    'ns_projects'  => ['nodes.id', 'ns_projects.node_id']
                ]
            ]
        );
    }

}