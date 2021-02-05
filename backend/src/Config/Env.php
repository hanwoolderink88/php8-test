<?php

namespace TestingTimes\Config;

/**
 * Class Config
 *
 * @package TestingTimes\Config
 */
class Env
{
    /**
     * @var array
     */
    private array $items;

    /**
     */
    public function __construct()
    {
        $this->items = $_ENV + $_SERVER;
    }

    /**
     * @param string $key
     * @param null $fallback
     * @return mixed|null
     */
    public function get(string $key, $fallback = null)
    {
        return $this->items[$key] ?? $fallback;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Env
     */
    public function set(string $key, mixed $value): self
    {
        $this->items[$key] = $value;

        return $this;
    }
}
