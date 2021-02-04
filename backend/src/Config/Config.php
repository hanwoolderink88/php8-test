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

    public function get(string $key){
        return $this->items[$key] ?? null;
    }

    public function set(string $key, mixed $value)
    {
        $this->items[$key] = $value;
    }
}
