<?php


namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Builder;

class MailingNode extends Node {

    /**
     * Determines the default edit link for node
     *
     * @param null|string $locale
     * @return string
     */
    public function getDefaultEditUrl($locale = null)
    {
        return route('reactor.mailings.edit', $this->getKey());
    }

    /**
     * Scopes the model for regular and mailing nodes
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTypeMailing(Builder $query)
    {
        return $query->where('mailing', 1);
    }

}