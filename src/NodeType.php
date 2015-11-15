<?php

namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Model as Eloquent;
use Nuclear\Hierarchy\Contract\Collection;
use Nuclear\Hierarchy\Contract\NodeTypeContract;

class NodeType extends Eloquent implements NodeTypeContract {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'label', 'description', 'visible', 'hides_children', 'color'];

    /**
     * Nodes relation
     *
     * @return HasMany
     */
    public function nodes()
    {
        return $this->hasMany(
            $this->getNodeModelPath()
        );
    }

    /**
     * Getter for node model path
     *
     * @return string
     */
    protected function getNodeModelPath()
    {
        return $this->nodeModelName ?: Node::class;
    }

    /**
     * Fields relation
     *
     * @return HasMany
     */
    public function fields()
    {
        return $this->hasMany(NodeField::class);
    }

    /**
     * Returns the name of the node type
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns collections ordered by position
     *
     * @return Collection
     */
    public function getFields()
    {
        return $this->fields()
            ->orderBy('position', 'asc')
            ->get();
    }

    /**
     * Add a field to the node type
     *
     * @param array $attributes
     * @return Eloquent
     */
    public function addField(array $attributes)
    {
        $field = $this->fields()->create($attributes);

        $this->save();

        return $field;
    }

    /**
     * Returns keys of the associated fields
     *
     * @return array
     */
    public function getFieldKeys()
    {
        return $this->getFields()->lists('name')->toArray();
    }

}