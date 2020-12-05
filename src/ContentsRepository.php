<?php

namespace Nuclear\Hierarchy;


class ContentsRepository {

    /** @var array */
    protected $contentsCache = [];

    /**
     * Returns the home content
     *
     * @param bool $recordView
     * @param bool $fail
     * @return SiteContent
     */
    public function getHome($recordView = true, $fail = true)
    {
        return $this->getContentById(config('app.home_content'), $fail, $recordView);
    }

    /**
     * Returns a content by id
     *
     * @param int $id
     * @param bool $fail
     * @param bool $recordView
     * @return SiteContent
     */
    public function getContentById($id, $fail = true, $recordView = true)
    {
    	if(isset($this->contentsCache[$id])) {
            $content = $this->contentsCache[$id];
        } else {
            $content = $fail ? SiteContent::findOrFail($id) : SiteContent::find($id);
            if($content) $this->contentsCache[(int)$content->id] = $content;
        }

        if($content && $recordView) $this->recordView($content);

    	return $content;
    }

    /**
     * Returns a content by name
     *
     * @param string $name
     * @param bool $fail
     * @param bool $recordView
     * @return SiteContent
     */
    public function getContentByName($name, $fail = true, $recordView = true)
    {
        $content = SiteContent::where('slug->' . app()->getLocale(), $name);

        $content = $fail ? $content->firstOrFail() : $content->first();
        if($content) $this->contentsCache[(int)$content->id] = $content;

        if($content && $recordView) $this->recordView($content);

        return $content;
    }

    /**
     * Records the content view
     *
     * @param SiteContent $content
     */
    protected function recordView(SiteContent $content = null)
    {
        if ($content) views($content)->collection(app()->getLocale())->cooldown(now()->addHours(2))->record();

        return $content;
    }

}