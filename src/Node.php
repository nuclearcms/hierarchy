<?php

namespace Nuclear\Hierarchy;

use Baum\Node as BaumNode;
use Dimsav\Translatable\Translatable;

class Node extends BaumNode {

    /**
     * The translatable trait requires some modification
     */
    use Translatable {
        isTranslationAttribute as _isTranslationAttribute;
    }

    /**
     * The translated fields for the model.
     */
    protected $translatedAttributes = ['title', 'node_name', 'source_type'];

    /**
     * The translation model is the NodeSource for us
     *
     * @var string
     */
    protected $translationModel = 'Nuclear\Hierarchy\NodeSource';

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isTranslationAttribute($key)
    {
        return $this->_isTranslationAttribute($key);
        // @todo implement isTranslationAttribute() method.
    }

}