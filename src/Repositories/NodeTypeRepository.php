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
     * Returns node types by ids
     *
     * @param array|string $ids
     * @return Collection
     */
    public function getNodeTypesByIds($ids)
    {
        if (empty($ids))
        {
            return null;
        }

        if (is_string($ids))
        {
            $ids = json_decode($ids, true);
        }

        if (is_array($ids) && ! empty($ids))
        {
            $model = $this->getModelName();

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $nodeTypes = $model::whereIn('id', $ids)
                ->orderByRaw('field(id,' . $placeholders . ')', $ids)
                ->get();

            return (count($nodeTypes) > 0) ? $nodeTypes : null;
        }

        return null;
    }

    /**
     * Getter for node type class name
     */
    public function getModelName()
    {
        return config('hierarchy.nodetype_model', 'Nuclear\Hierarchy\NodeType');
    }

}