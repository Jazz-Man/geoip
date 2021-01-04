<?php

use JazzMan\GeoIP\GeoData;
use JazzMan\GeoIP\GeoIP;

if (! \function_exists('app_user_geo_data')) {
    /**
     * @return false|GeoData
     */
    function app_user_geo_data()
    {
        /* @var GeoIP $user_geo_data */
        static $user_geo_data;
        if (empty($user_geo_data)) {
            $user_geo_data = GeoIP::getInstance();
        }

        return $user_geo_data->get_geo_data();
    }
}
