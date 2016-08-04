<?php

namespace Nuclear\Hierarchy\Support;


use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Session\SessionManager;

class LocaleManager {

    /** @var Repository */
    protected $config;

    /** @var Application */
    protected $app;

    /** @var SessionManager */
    protected $session;

    /**
     * Constructor
     *
     * @param Application $app
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct(Application $app, SessionManager $session, Repository $config)
    {
        $this->config = $config;
        $this->app = $app;
        $this->session = $session;
    }

    /**
     * Sets the app locale
     *
     * @param string $locale
     * @param bool $session
     * @return void
     */
    public function setAppLocale($locale = null, $session = true)
    {
        $locale = $locale ?: $this->session->get('_locale', null);

        if ($locale)
        {
            $this->app->setLocale($locale);

            if ($session)
            {
                $this->session->put('_locale', $locale);
            }

            $this->setTimeLocale($locale);
        }
    }

    /**
     * Sets the time locale
     *
     * @param string $locale
     * @return void
     */
    public function setTimeLocale($locale = null)
    {
        $locale = $locale ?: $this->session->get('_locale', $this->app->getLocale());

        setlocale(LC_TIME, $this->config->get('app.full_locales.' . $locale, null));

        Carbon::setLocale($locale);
    }

}