<?php

use Nuclear\Hierarchy\Bags\NodeTypeBag;

class NodeTypeBagTest extends TestBase {

    /**
     * Returns a new NodeTypeBag instance
     */
    protected function getNodeTypeBag()
    {
        return new NodeTypeBag;
    }

    /**
     * Returns a node type mock
     *
     * @param int $id
     * @return object
     */
    protected function getNodeTypeMock($id = 1)
    {
        $nodeType = $this->prophesize('Nuclear\Hierarchy\Contract\NodeTypeContract');
        $nodeType->getKey()
            ->willReturn($id)
            ->shouldBeCalled();

        return $nodeType->reveal();
    }

    /** @test */
    function it_adds_and_gets_a_node_type()
    {
        $bag = $this->getNodeTypeBag();

        $this->assertNull(
            $bag->getNodeType(1)
        );

        $nodeType = $this->getNodeTypeMock();

        $bag->addNodeType($nodeType);

        $this->assertEquals(
            1,
            $bag->getNodeType(1)->getKey()
        );
    }

    /** @test */
    function it_checks_if_has_node_type()
    {
        $bag = $this->getNodeTypeBag();

        $this->assertNull(
            $bag->getNodeType(1)
        );

        $this->assertFalse(
            $bag->hasNodeType(1)
        );

        $nodeType = $this->getNodeTypeMock();

        $bag->addNodeType($nodeType);

        $this->assertTrue(
            $bag->hasNodeType(1)
        );
    }

}