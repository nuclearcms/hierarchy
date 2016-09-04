<?php


namespace Nuclear\Hierarchy;


class MailingNode extends Node {

    /**
     * Determines if the model type is mailing
     *
     * @var bool
     */
    protected $isTypeMailing = true;

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

}