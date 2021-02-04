<?php

namespace TestingTimes\Http\Utils;

/**
 * Class Request
 *
 * @package TestingTimes\Http\Utils
 */
class RequestHelper
{
    /**
     * @return array
     */
    public static function getRequestHeaders(): array
    {
        // only when using apache
        if (function_exists('apache_request_headers')) {
            return (array) apache_request_headers();
        }

        // else we get them from the server super global
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }

        return $headers;
    }
}
