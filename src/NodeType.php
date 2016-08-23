<?php

namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kenarkose\Chronicle\RecordsActivity;
use Kenarkose\Sortable\Sortable;
use Nicolaslopezj\Searchable\SearchableTrait;
use Nuclear\Hierarchy\Contract\Collection;
use Nuclear\Hierarchy\Contract\NodeTypeContract;

class NodeType extends Eloquent implements NodeTypeContract {

    use Sortable, SearchableTrait, RecordsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'label',
        'visible', 'hides_children', 'color',
        'taggable', 'mailing', 'allowed_children', 'custom_form'];

    /**
     * Sortable columns
     *
     * @var array
     */
    protected $sortableColumns = ['name', 'label'];

    /**
     * Default sortable key
     *
     * @var string
     */
    protected $sortableKey = 'name';

    /**
     * Default sortable direction
     *
     * @var string
     */
    protected $sortableDirection = 'asc';

    /**
     * Searchable columns.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'name'  => 10,
            'label' => 10
        ]
    ];

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

    /**
     * Checks if the node type is mailing type
     *
     * @return bool
     */
    public function isTypeMailing()
    {
        return (bool)$this->mailing;
    }

    /**
     * Scope for selecting types for nodes
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeForNodes(Builder $query)
    {
        return $query->whereMailing(0);
    }

    /**
     * Scope for selecting types for mailings
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeForMailings(Builder $query)
    {
        return $query->whereMailing(1);
    }

    /**
     * Returns the node type title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->label;
    }

}