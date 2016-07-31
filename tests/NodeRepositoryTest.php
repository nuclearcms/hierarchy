<?php


class NodeRepositoryTest extends TestBase {

    protected function getNodeRepository()
    {
        return $this->app->make('Nuclear\Hierarchy\NodeRepository');
    }

    /** @test */
    function it_is_instantiatable()
    {
        $this->assertInstanceOf(
            'Nuclear\Hierarchy\NodeRepository',
            $this->getNodeRepository()
        );
    }

    /** @test */
    function it_gets_search_node_builder()
    {
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Builder',
            $this->getNodeRepository()->getSearchNodeBuilder('derp')
        );
    }

}