<?php

namespace JazzMan\GeoIP;

class GeoData
{
    /**
     * @var string| boolean
     */
    public $addr = false;
    /**
     * @var string|boolean
     */
    public $country_code = false;

    /**
     * @var string|boolean
     */
    public $country_name = false;

    /**
     * @var string|boolean
     */
    public $continent_code = false;

    /**
     * @var int|boolean
     */
    public $region = false;

    /**
     * @var string|boolean
     */
    public $region_name = false;

    /**
     * @var string|float|boolean
     */
    public $latitude = false;

    /**
     * @var string|float|boolean
     */
    public $longitude = false;

    /**
     * @var string|false
     */
    public $time_zone = false;

    /**
     * @var string|int|boolean
     */
    public $postal_code = false;

    /**
     * @var string|boolean
     */
    public $city = false;

    /**
     * @param  string  $prop
     * @param  string|int  $val
     */
    public function __set(string $prop, $val)
    {
        switch ($prop) {
            case 'addr':
                $this->$prop = GeoIP::validate_ip($val);
                break;
            case 'region':
            case 'postal_code':
                $this->$prop = GeoIP::validate_int($val);
                break;
            default:
                $this->$prop = GeoIP::validate_string($val);
                break;
        }
    }
}
