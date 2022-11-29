<?php

namespace Nuclear\Hierarchy;

use Illuminate\Database\Eloquent\Model;

trait HasSlug
{
    public static function bootHasSlug()
    {
        static::saving(function (Model $model) {
            collect($model->getTranslatedLocales('title'))
                ->each(function (string $locale) use ($model) {
                    if(empty($model->getTranslation('slug', $locale, false))) {
                        $model->setTranslation('slug', $locale, $model->generateSlug($locale));
                    }
                });
        });
    }

    protected function generateSlug(string $locale): string
    {
        return call_user_func('\Illuminate\Support\Str::slug', $this->getTranslation('title', $locale));
    }
}
