<?php

namespace Nuclear\Hierarchy;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Nicolaslopezj\Searchable\SearchableTrait;

trait Searchable {

    use SearchableTrait
    {
        getColumns as _getColumns;
        getGroupBy as _getGroupBy;
        getTableColumns as _getTableColumns;
        getJoins as _getJoins;
    }

    /**
     * Returns the search columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        if (array_key_exists('columns', $this->getSearchable()))
        {
            $driver = $this->getDatabaseDriver();
            $prefix = Config::get("database.connections.$driver.prefix");
            $columns = [];
            foreach ($this->getSearchable()['columns'] as $column => $priority)
            {
                $columns[$prefix . $column] = $priority;
            }

            return $columns;
        } else
        {
            return DB::connection()->getSchemaBuilder()->getColumnListing($this->table);
        }
    }

    /**
     * Returns whether or not to keep duplicates.
     *
     * @return array
     */
    protected function getGroupBy()
    {
        if (array_key_exists('groupBy', $this->getSearchable()))
        {
            return $this->getSearchable()['groupBy'];
        }

        return false;
    }

    /**
     * Returns the table columns.
     *
     * @return array
     */
    public function getTableColumns()
    {
        return $this->getSearchable()['table_columns'];
    }

    /**
     * Returns the tables that are to be joined.
     *
     * @return array
     */
    protected function getJoins()
    {
        return array_get($this->getSearchable(), 'joins', []);
    }

}