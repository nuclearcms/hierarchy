<?php

namespace Nuclear\Hierarchy\Tags;


class TagRepository {

    /**
     * Returns a tag by name
     *
     * @param string $name
     * @return Node
     */
    public function getTag($name)
    {
        return Tag::whereTranslation('tag_name', $name)
            ->firstOrFail();
    }

    /**
     * Returns a tag by name and sets the locale
     *
     * @param $name
     * @return Tag
     */
    public function getTagAndSetLocale($name)
    {
        $tag = $this->getTag($name);

        $locale = $tag->getLocaleForName($name);

        set_app_locale($locale);

        return $tag;
    }

}