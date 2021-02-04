<?php
declare(strict_types=1);

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
        $nonParsed = $_SERVER;
        $headers = [];

        foreach ($nonParsed as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[strtolower($header)] = $value;
        }

        return $headers;
    }
}
