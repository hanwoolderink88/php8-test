<?php
declare(strict_types=1);

namespace TestingTimes\Routing;

/**
 * When a route has wildcards the route should be split into parts so it can be handled when matching
 */
class RoutePart
{
    /**
     * @var string
     */
    protected string $string;

    /**
     * @var bool
     */
    protected bool $isWildcard = false;

    /**
     * RoutePart constructor.
     * @param string $part
     */
    public function __construct(string $part)
    {
        if (str_contains($part, '{')) {
            $this->isWildcard = true;
            $this->string = (string)str_replace(['{', '}'], '', $part);
        } else {
            $this->string = $part;
        }
    }

    /**
     * @return string|string[]
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @return bool
     */
    public function isWildcard(): bool
    {
        return $this->isWildcard;
    }
}
