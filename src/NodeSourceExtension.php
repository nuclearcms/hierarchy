<?php

namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Model as Eloquent;
use Nuclear\Synthesizer\Synthesizer;

class NodeSourceExtension extends Eloquent {

    /*
     * Timestamps for the model.
     */
    public $timestamps = false;

    /**
     * Custom mutations
     *
     * @var array
     */
    protected $mutations = [];

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

    /**
     * Get an attribute from the model with custom accessor.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (isset($this->mutations[$key]))
        {
            return $this->mutations[$key];
        }

        if (array_key_exists($key, $mutatables = static::getMutatables()))
        {
            return $this->mutateExtensionAttribute($key, $mutatables[$key]);
        }

        return parent::getAttribute($key);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if (isset($this->mutations[$key]))
        {
            unset($this->mutations[$key]);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Mutates and stores an attribute in array
     *
     * @param string $key
     * @param string $type
     * @return mixed
     */
    protected function mutateExtensionAttribute($key, $type)
    {
        $value = $this->getAttributeFromArray($key);

        $mutation = $this->{'make' . studly_case($type) . 'TypeMutation'}($value);

        $this->mutations[$key] = $mutation;

        return $mutation;
    }

    /**
     * Makes a document type mutation
     *
     * @param mixed $value
     * @return Media
     */
    protected function makeDocumentTypeMutation($value)
    {
        return get_nuclear_documents($value);
    }

    /**
     * Makes a gallery type mutation
     *
     * @param mixed $value
     * @return Media
     */
    protected function makeGalleryTypeMutation($value)
    {
        return get_nuclear_gallery($value);
    }

    /**
     * Makes a markdown type mutation
     *
     * @param mixed $value
     * @return Media
     */
    protected function makeMarkdownTypeMutation($value)
    {
        return app()->make(Synthesizer::class)->setText($value);
    }

    /**
     * Makes a node type mutation
     *
     * @param mixed $value
     * @return Media
     */
    protected function makeNodeTypeMutation($value)
    {
        return node_bag($value);
    }

    /**
     * Makes a node type mutation
     *
     * @param mixed $value
     * @return Media
     */
    protected function makeNodeCollectionTypeMutation($value)
    {
        return get_nodes_by_ids($value);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = [];

        foreach ($this->fillable as $attribute)
        {
            $attributes[$attribute] = $this->getAttributeFromArray($attribute);
        }

        return $attributes;
    }

}