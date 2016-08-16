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
     * @param string $name
     * @param string $locale
     * @return Tag
     */
    public function getTagAndSetLocale($name, $locale = null)
    {
        $tag = $this->getTag($name);

        // Override locale or use tag locale
        // this is for when a tag does not have to be translated
        // and the app locale is determined via route parameter (etiket|tag)
        $locale = $locale ?: $tag->getLocaleForName($name);

        set_app_locale($locale);

        return $tag;
    }

}