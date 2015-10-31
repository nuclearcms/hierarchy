<?php

use Nuclear\Hierarchy\NodeSource;

class NodeSourceTest extends TestBase {

    protected function getNodeSource($attributes = [])
    {
        $attributes = !empty($attributes) ? $attributes : [
            'title' => 'Node Title',
            'node_name' => 'node-title',
            'locale' => 'en',
            'source_type' => 'project'
        ];

        return new NodeSource($attributes);
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

}