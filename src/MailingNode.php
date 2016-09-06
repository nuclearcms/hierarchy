<?php


namespace Nuclear\Hierarchy;


use Illuminate\Database\Eloquent\Builder;
use Nuclear\Hierarchy\Mailings\MailingList;

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

    /**
     * Lists relation
     *
     * @return Relation
     */
    public function lists()
    {
        return $this->belongsToMany(MailingList::class, 'mailing_list_node', 'node_id' , 'mailing_list_id');
    }

    /**
     * Link a list to subscriber
     *
     * @param int $id
     * @return MailingList
     */
    public function associateList($id)
    {
        return $this->lists()->attach(
            MailingList::findOrFail($id)
        );
    }

    /**
     * Unlink a list from subscriber
     *
     * @param int $id
     * @return MailingList
     */
    public function dissociateList($id)
    {
        return $this->lists()->detach(
            MailingList::findOrFail($id)
        );
    }

}