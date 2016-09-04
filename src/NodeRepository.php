<?php

namespace Nuclear\Hierarchy;


use Nuclear\Hierarchy\Support\TokenManager;

class NodeRepository {

    /** @var TokenManager */
    protected $tokenManager;

    /**
     * Constructor
     *
     * @param TokenManager $tokenManager
     */
    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * Returns the home node
     *
     * @param bool $track
     * @return Node
     */
    public function getHome($track = true)
    {
        $home = PublishedNode::whereHome(1)
            ->firstOrFail();

        set_app_locale();

        $this->track($track, $home);

        return $home;
    }

    /**
     * Returns a node by name
     *
     * @param string $name
     * @param bool $track
     * @param bool $published
     * @return Node
     */
    public function getNode($name, $track = true, $published = true)
    {
        if ($this->withPublishedOnly($published))
        {
            $node = PublishedNode::withName($name);
        } else
        {
            $node = Node::withName($name);
        }

        $node = $node->firstOrFail();

        $this->track($track, $node);

        return $node;
    }

    /**
     * Checks if the request includes unpublished nodes as well
     *
     * @param bool $published
     * @return bool
     */
    protected function withPublishedOnly($published)
    {
        if ($published === false)
        {
            return false;
        }

        if ($this->tokenManager->requestHasToken('preview_nodes'))
        {
            return false;
        }

        return true;
    }

    /**
     * Returns a node by name and sets the locale
     *
     * @param string $name
     * @param bool $track
     * @param bool $published
     * @return Node
     */
    public function getNodeAndSetLocale($name, $track = true, $published = true)
    {
        $node = $this->getNode($name, $track, $published);

        $locale = $node->getLocaleForNodeName($name);

        set_app_locale($locale);

        return $node;
    }

    /**
     * Gets node searching builder
     *
     * @param string $keywords
     * @param int $limit
     * @param string $locale
     * @return Builder
     */
    public function getSearchNodeBuilder($keywords, $limit = null, $locale = null)
    {
        // Because of the searchable trait we have to reset global scopes
        $builder = PublishedNode::withoutGlobalScopes()
            ->published()
            ->typeMailing()
            ->translatedIn($locale)
            ->groupBy('id')
            ->search($keywords, 20, true);

        if ( ! is_null($limit))
        {
            $builder->limit($limit);
        }

        return $builder;
    }

    /**
     * Searches for nodes
     *
     * @param string $keywords
     * @param int $limit
     * @param string $locale
     * @return Collection
     */
    public function searchNodes($keywords, $limit = null, $locale = null)
    {
        return $this->getSearchNodeBuilder($keywords, $limit, $locale)->get();
    }

    /**
     * Returns a node by id
     *
     * @param int $id
     * @param bool $published
     * @return Node
     */
    public function getNodeById($id, $published)
    {
        return $published ? PublishedNode::find($id) : Node::find($id);
    }

    /**
     * Returns nodes by ids
     *
     * @param array|string $ids
     * @param bool $published
     * @return Collection
     */
    public function getNodesByIds($ids, $published = true)
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
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $nodes = Node::whereIn('id', $ids)
                ->orderByRaw('field(id,' . $placeholders . ')', $ids);

            if ($published)
            {
                $nodes->published();
            }

            $nodes = $nodes->get();

            return (count($nodes) > 0) ? $nodes : null;
        }

        return null;
    }

    /**
     * Tracks the node
     *
     * @param $track
     * @param $node
     */
    protected function track($track, $node)
    {
        if ($track)
        {
            tracker()->addTrackable($node);
        }
    }

}