<?php

namespace ModernRetail\Base\lib;

class MRApi
{

    protected static $instance = null;
    const API_BASE_URl = "http://api.oleg.modernretail.com/client";

    const GOOGLE_MAPS_GEOCODE_URl = "https://maps.googleapis.com/maps/api/geocode/json";

    const SANDBOX_API_BASE_URl = "http://api.oleg.modernretail.com";

    protected $_login;
    protected $_password;
    protected $_apiUrl;

    public function getApiUrl()
    {
        return $this->_apiUrl;
    }

    protected function __construct($login, $password, $isSandbox)
    {
        $this->_login = $login;
        $this->_password = $password;
        if ($isSandbox === true) {
            $this->_apiUrl = self::SANDBOX_API_BASE_URl;
        } else {
            $this->_apiUrl = self::API_BASE_URl;
        }
    }

    protected function __clone()
    {
    }

    public static function login($login, $password, $isSandbox = false)
    {
        static::$instance = new static($login, $password, $isSandbox);
    }

    public static function sandbox($login, $password)
    {
        self::login($login, $password, true);
    }


    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }


    public static function call($method = "GET", $endpoint, $jsonData = null)
    {
        return self::getInstance()->apicall($method, $endpoint, $jsonData);
    }


    public function apicall($method = "GET", $endpoint, $jsonData = null)
    {

        if ($endpoint["0"] == "/") {
            $endpoint = substr($endpoint, 1);
        }

        $fullUrl = $this->getApiUrl() . "/" . $endpoint;

        $ch = curl_init();

        $timeout = 15;
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method == "POST") {
            // curl_setopt($ch,  CURLOPT_POST, true);
        }

        if ($jsonData) {
            $json = json_encode($jsonData);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);


        /**
         * adding auth
         */
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_login . ":" . $this->_password);


        $raw_data = curl_exec($ch);

        curl_close($ch);

        $data = json_decode($raw_data, true);


        if (is_null($data)) {
            d($raw_data);
        }

        if (array_key_exists('status', $data) && ($data['status'] == 'Error' || $data['status'] == 'ERROR')) {
            // $this->monitorApiLogger->info($data['message'],$jsonData);
            throw new \Exception($data['message']);
            //throw new \Exception($data['message']);
        }
        return $data;


    }

    public static function GET($endpoint)
    {
        return self::call("GET", $endpoint, null);
    }

    public static function POST($endpoint, $data = null)
    {
        return self::call("POST", $endpoint, $data);
    }

    public static function PUT($endpoint, $data = null)
    {
        return self::call("PUT", $endpoint, $data);
    }

    public static function MAPGEOCODE($params)
    {
        $method = "GET";

        $fullUrl = self::GOOGLE_MAPS_GEOCODE_URl . "?" . $params;

//        dd($fullUrl);

        $ch = curl_init();

        $timeout = 15;
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        $raw_data = curl_exec($ch);

        curl_close($ch);

        $data = json_decode($raw_data, true);

        if (is_null($data)) {
            d($raw_data);
        }

        if (array_key_exists('status', $data) && ($data['status'] == 'INVALID_REQUEST')) {
            throw new \Exception($data['error_message']);
        }

        return $data['results'];
    }

}