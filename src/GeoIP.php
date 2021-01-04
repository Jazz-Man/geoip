<?php

namespace JazzMan\GeoIP;

use JazzMan\ParameterBag\ParameterBag;
use JazzMan\Traits\SingletonTrait;
use MaxMind\Db\Reader;

class GeoIP
{
    use SingletonTrait;

    const GEO_DB = '/usr/share/GeoIP/GeoLite2-City.mmdb';

    private static $headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
        'GEOIP_ADDR',
        'HTTP_X_REAL_IP',
    ];

    private $user_data = false;
    /**
     * @var GeoData
     */
    private $geo_data;

    private function __construct()
    {
        $user_data = self::get_user_data_from_server();

        $this->geo_data = new GeoData();

        if (! empty($user_data)) {
            foreach ($user_data as $key => $val) {
                $prop = \ltrim(\strtolower($key), 'geoip_');
                $this->geo_data->$prop = $val;
            }
        } else {
            $user_ip = self::get_ip_address();

            if (! empty($user_ip) && \class_exists('\MaxMind\Db\Reader')) {
                $reader = new Reader(self::GEO_DB);

                try {
                    $user_data = $reader->get($user_ip);
                    $reader->close();

                    $this->geo_data->addr = $user_ip;

                    $data = new ParameterBag($user_data);

                    $this->geo_data->city = $data->get('city.names.en', false);

                    $this->geo_data->country_code = $data->get('country.iso_code', false);
                    $this->geo_data->country_name = $data->get('country.names.en', false);
                    $this->geo_data->continent_code = $data->get('continent.code', false);

                    $this->geo_data->latitude = $data->get('location.latitude', false);
                    $this->geo_data->longitude = $data->get('location.longitude', false);
                    $this->geo_data->time_zone = $data->get('location.time_zone', false);
                    $this->geo_data->postal_code = $data->get('postal.code', false);
                    $this->geo_data->region = $data->get('subdivisions.0.iso_code', false);
                    $this->geo_data->region_name = $data->get('subdivisions.0.names.en', false);
                } catch (\Exception $e) {
                    \error_log($e);
                }
            }
        }
    }

    /**
     * Get current user IP Address.
     *
     * @return string|false
     */
    public static function get_ip_address()
    {
        foreach (self::$headers as $header) {
            if (! empty($_SERVER[$header])) {
                foreach (\explode(',', $_SERVER[$header]) as $ip) {
                    $ip = \trim($ip);

                    if (self::validate_ip($ip) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $ip
     *
     * @return string|boolean
     */
    public static function validate_ip(string $ip)
    {
        $ip_validation = self::ip_validator_options();

        return \filter_var($ip, $ip_validation['filter'], $ip_validation['flags']);
    }

    public static function ip_validator_options(): array
    {
        return [
            'filter' => FILTER_VALIDATE_IP,
            'flags' => FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ];
    }

    /**
     * @return array
     */
    public static function get_user_data_from_server(): array
    {
        return \filter_input_array(INPUT_SERVER, [
            'GEOIP_ADDR' => self::ip_validator_options(),
            'GEOIP_COUNTRY_CODE' => self::string_validator_options(),
            'GEOIP_COUNTRY_NAME' => self::string_validator_options(),
            'GEOIP_CONTINENT_CODE' => self::string_validator_options(),
            'GEOIP_REGION_NAME' => self::string_validator_options(),
            'GEOIP_LATITUDE' => self::string_validator_options(),
            'GEOIP_LONGITUDE' => self::string_validator_options(),
            'GEOIP_TIME_ZONE' => self::string_validator_options(),
            'GEOIP_CITY' => self::string_validator_options(),
            'GEOIP_REGION' => self::int_validator_options(),
            'GEOIP_POSTAL_CODE' => self::int_validator_options(),
        ], false);
    }

    public static function string_validator_options(): array
    {
        return [
            'filter' => FILTER_SANITIZE_STRING,
            'flags' => FILTER_REQUIRE_SCALAR,
        ];
    }

    public static function int_validator_options(): array
    {
        return [
            'filter' => FILTER_VALIDATE_INT,
            'flags' => FILTER_REQUIRE_SCALAR,
        ];
    }

    /**
     * @param $string
     *
     * @return string|false
     */
    public static function validate_string($string)
    {
        $ip_validation = self::string_validator_options();

        return \filter_var($string, $ip_validation['filter'], $ip_validation['flags']);
    }

    /**
     * @param $string
     *
     * @return int|false
     */
    public static function validate_int($string)
    {
        $ip_validation = self::int_validator_options();

        return \filter_var($string, $ip_validation['filter'], $ip_validation['flags']);
    }

    /**
     * @return false|GeoData
     */
    public function get_geo_data()
    {
        return $this->geo_data;
    }
}
