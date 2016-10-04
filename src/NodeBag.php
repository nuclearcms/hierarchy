<?php


namespace Nuclear\Hierarchy;


use Illuminate\Support\Collection;

class NodeBag extends Collection {

    /**
     * Gets or finds the node and sets by id
     *
     * @param int $id
     * @param bool $published
     * @return Node
     */
    public function getOrFind($id, $published = true)
    {
        $node = $this->get($id);

        if (is_null($node))
        {
            $node = $published ?
                PublishedNode::find($id) :
                Node::find($id);

            $this->put($id, $node);
        }

        return $node;
    }

}