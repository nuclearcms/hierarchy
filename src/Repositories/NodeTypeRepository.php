<?php

namespace Nuclear\Hierarchy\Repositories;


class NodeTypeRepository extends Repository {

    /**
     * Creates a node type
     *
     * @param array $attributes
     * @return NodeTypeContract
     */
    public function create(array $attributes)
    {
        $model = $this->getModelName();

        $nodeType = $model::create($attributes);

        $this->builderService->buildTable(
            $nodeType->getName(),
            $nodeType->getKey()
        );

        return $nodeType;
    }

    /**
     * Destroys a node type
     *
     * @param int $id
     * @return NodeTypeContract
     */
    public function destroy($id)
    {
        $model = $this->getModelName();

        $nodeType = $model::findOrFail($id);

        $this->builderService->destroyTable(
            $nodeType->getName(),
            $nodeType->getFieldKeys(),
            $nodeType->getKey()
        );

        $nodeType->delete();

        return $nodeType;
    }

    /**
     * Getter for node type class name
     */
    public function getModelName()
    {
        return config('hierarchy.nodetype_model', 'Nuclear\Hierarchy\NodeType');
    }

}