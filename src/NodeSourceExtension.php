<?php

namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Model as Eloquent;

class NodeSourceExtension extends Eloquent {

    /*
     * Timestamps for the model.
     */
    public $timestamps = false;

    /**
     * Sets the node id
     *
     * @param int $id
     * @return self
     */
    public function setNodeId($id)
    {
        $this->setAttribute('node_id', $id);

        return $this;
    }

}