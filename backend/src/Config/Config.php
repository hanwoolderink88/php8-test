<?php

namespace TestingTimes\Config;

/**
 * Class Config
 *
 * @package TestingTimes\Config
 */
class Config
{
    private array $items;

    public function __construct()
    {
        $this->items = $_ENV + $_SERVER;
    }

    public function get(string $key, $fallback = null)
    {
        return $this->items[$key] ?? $fallback;
    }

    public function set(string $key, mixed $value)
    {
        $this->items[$key] = $value;
    }
}
