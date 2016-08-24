<?php

use Nuclear\Hierarchy\Builders\MigrationBuilder;

if ( ! function_exists('generated_path'))
{
    /**
     * Get the path to the generated folder.
     *
     * @param string $path
     * @return string
     */
    function generated_path($path = '')
    {
        return app()->make('path.generated') . ($path ? '/' . $path : $path);
    }
}

if ( ! function_exists('source_model_name'))
{
    /**
     * Returns the name of the source model by key
     *
     * @param string $key
     * @param bool $withPath
     * @return string
     */
    function source_model_name($key, $withPath = false)
    {
        $name = studly_case(MigrationBuilder::TABLE_PREFIX . $key);

        return $withPath ? 'gen\\Entities\\' . $name : $name;
    }
}

if ( ! function_exists('source_form_name'))
{
    /**
     * Returns the name of the source form by key
     *
     * @param string $key
     * @param bool $withNamespace
     * @return string
     */
    function source_form_name($key, $withNamespace = false)
    {
        return ($withNamespace ? 'gen\\Forms\\' : '') . 'Edit' . ucfirst($key) . 'Form';
    }
}

if ( ! function_exists('source_table_name'))
{
    /**
     * Returns the name of the source table by key
     *
     * @param string $key
     * @return string
     */
    function source_table_name($key)
    {
        return str_plural(MigrationBuilder::TABLE_PREFIX . $key);
    }
}

if ( ! function_exists('hierachy_bag'))
{
    /**
     * Returns a hierarchy bag
     *
     * @param string $bag
     * @return object
     */
    function hierarchy_bag($bag)
    {
        return app()->make('hierarchy.bags.' . $bag);
    }
}

if ( ! function_exists('get_node_by_id'))
{
    /**
     * Returns the node by given id
     * (alias for NodeRepository::getNodeById)
     *
     * @param int $id
     * @param bool $published
     * @return Node
     */
    function get_node_by_id($id, $published = true)
    {
        return app()->make('Nuclear\Hierarchy\NodeRepository')
            ->getNodeById($id, $published);
    }
}

if ( ! function_exists('get_nodes_by_ids'))
{
    /**
     * Returns the nodes by given ids
     * (alias for NodeRepository::getNodesByIds)
     *
     * @param array|string $ids
     * @param bool $published
     * @return Collection
     */
    function get_nodes_by_ids($ids, $published = true)
    {
        return app()->make('Nuclear\Hierarchy\NodeRepository')
            ->getNodesByIds($ids, $published);
    }
}

{
    /**
     * Returns the node types by given ids
     * (alias for NodeTypeRepository::getNodeTypesByIds)
     *
     * @param array|string $ids
     * @return Collection
     */
    function get_nodetypes_by_ids($ids)
    {
        return app()->make('Nuclear\Hierarchy\Repositories\NodeTypeRepository')
            ->getNodeTypesByIds($ids);
    }
}

if ( ! function_exists('set_app_locale'))
{
    /**
     * Sets the app locale
     *
     * @param string $locale
     * @param bool $session
     * @return void
     */
    function set_app_locale($locale = null, $session = true)
    {
        app('hierarchy.support.locale')->setAppLocale($locale, $session);
    }
}

if ( ! function_exists('set_time_locale'))
{
    /**
     * Sets the time locale
     *
     * @param string $locale
     * @return void
     */
    function set_time_locale($locale = null)
    {
        app('hierarchy.support.locale')->setTimeLocale($locale);
    }

}

if ( ! function_exists('locales'))
{
    /**
     * Returns app locales
     *
     * @return array
     */
    function locales()
    {
        return config('translatable.locales');
    }
}

if ( ! function_exists('locale_count'))
{
    /**
     * Returns the locale count of the app
     *
     * @return int
     */
    function locale_count()
    {
        return count(config('translatable.locales'));
    }
}