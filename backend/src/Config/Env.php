<?php
declare(strict_types=1);

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
     * @param  string  $key
     * @param  null  $fallback
     * @return mixed|null
     */
    public function get(string $key, $fallback = null)
    {
        if (!isset($this->items[$key])) {
            return $fallback;
        }

        $value = $this->items[$key];

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float)$value : (int)$value;
        }

        if (str_contains($value, '[') && str_contains($value, ']')) {
            return explode(',', str_replace(['[', ']', ' '], '', $value));
        }

        return $value;
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @return Env
     */
    public function set(string $key, mixed $value): self
    {
        $this->items[$key] = $value;

        return $this;
    }
}
