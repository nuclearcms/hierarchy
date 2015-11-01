<?php

namespace Nuclear\Hierarchy\Cache;


use Nuclear\Hierarchy\Contract\Cache\AccessorContract;
use Nuclear\Hierarchy\Support\FileKeeper;

class Accessor implements AccessorContract {

    /**
     * Caches the fields into the app
     *
     * @var array
     */
    protected $fields = null;

    /**
     * Reads the cache
     *
     * @return array
     */
    public function read()
    {
        if (is_null($this->fields))
        {
            $this->createCacheIfNotExists();

            $contents = FileKeeper::read(
                $this->getCacheFilePath()
            );

            $this->fields = json_decode($contents, true);
        }


        return $this->fields;
    }

    /**
     * Creates cache file if it does not exist
     */
    protected function createCacheIfNotExists()
    {
        if ( ! FileKeeper::exists(
            $this->getCacheFilePath()
        ))
        {
            $this->write([]);
        }
    }

    /**
     * Writes the cache
     *
     * @param array $data
     */
    public function write(array $data)
    {
        $this->fields = $data;

        FileKeeper::write(
            $this->getCacheFilePath(),
            json_encode($this->fields)
        );
    }

    /**
     * Getter for fields in node type
     *
     * @param int $id
     * @return array
     */
    public function getFieldsFor($id)
    {
        return $this->read()[$id];
    }

    /**
     * Checks if a node type has key
     *
     * @param int $id
     * @param string $field
     * @return bool
     */
    public function nodeTypeHasField($id, $field)
    {
        return in_array(
            $field,
            $this->getFieldsFor($id)
        );
    }

    /**
     * Getter for the cache file path
     *
     * @return string
     */
    protected function getCacheFilePath()
    {
        return generated_path('fillables_cache.json');
    }

}