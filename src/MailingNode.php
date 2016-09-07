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
        return $this->belongsToMany(
            MailingList::class,
            'mailing_list_node',
            'node_id', 'mailing_list_id')
            ->withPivot('external_mailing_id');
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

    /**
     * Getter for the external mailing id
     *
     * @return string
     */
    public function getExternalId($id)
    {
        return $this->lists->find($id)
            ->pivot->external_mailing_id;
    }

    /**
     * Getter for the external mailing id
     *
     * @param int $id
     * @param string $value
     * @return string
     */
    public function setExternalId($id, $value)
    {
        return $this->lists()->updateExistingPivot($id, ['external_mailing_id' => $value]);
    }

}