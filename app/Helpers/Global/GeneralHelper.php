<?php

use Carbon\Carbon;

if (! function_exists('appName')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function appName()
    {
        return strpos($_SERVER["HTTP_HOST"], "bannerstudio") === false ? "RapidAds" : "BannerStudio";
    }
}

if (! function_exists('siteUrl')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function siteUrl()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $url = $protocol . "://$_SERVER[HTTP_HOST]";
        
        return $url;
    }
}

if (! function_exists('carbon')) {
    /**
     * Create a new Carbon instance from a time.
     *
     * @param $time
     *
     * @return Carbon
     * @throws Exception
     */
    function carbon($time)
    {
        return new Carbon($time);
    }
}

if (! function_exists('homeRoute')) {
    /**
     * Return the route to the "home" page depending on authentication/authorization status.
     *
     * @return string
     */
    function homeRoute()
    {
        if (config('boilerplate.access.user.redirect') && auth()->check()) {
            if (auth()->user()->isMasterAdmin()) {
                return 'admin.dashboard';
            }

            return 'frontend.banner.index';
        }

        return 'frontend.index';
    }
}
