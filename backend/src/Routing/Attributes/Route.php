<?php
declare(strict_types=1);

namespace TestingTimes\Routing\Attributes;

use Attribute;
use TestingTimes\Routing\Exceptions\RouterMatchException;
use TestingTimes\Routing\RoutePart;

/**
 * A route is path/method combination and a class/method combination resolving to each other..
 */
#[Attribute]
class Route
{
    /** @var mixed|array|callable */
    protected $callable;

    /** @var bool */
    protected bool $wildcard;

    /** @var RoutePart[] */
    protected array $routeParts = [];

    /**
     * @param string $path
     * @param string[] $methods
     */
    public function __construct(
        protected string $path,
        protected array $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
    ) {
        $this->path = ltrim(rtrim($this->path, '/'), '/');
        $this->wildcard = str_contains($this->path, '{');

        if ($this->wildcard === true) {
            $this->routeParts = array_map(fn($part) => new RoutePart($part), explode('/', $this->path));
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Route
     */
    public function setPath(string $path): Route
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param string[] $methods
     * @return Route
     */
    public function setMethods(array $methods): Route
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return array
     */
    public function getCallable(): array
    {
        return $this->callable;
    }

    /**
     * @param array|callable|mixed $callable
     * @return Route
     */
    public function setCallable(mixed $callable): Route
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasWildcard(): bool
    {
        return $this->wildcard;
    }

    /**
     * @return RoutePart[]
     */
    public function getRouteParts(): array
    {
        return $this->routeParts;
    }

    /**
     * @param RoutePart[] $routeParts
     * @return Route
     */
    public function setRouteParts(array $routeParts): self
    {
        $this->routeParts = $routeParts;

        return $this;
    }

    /**
     * @param string[] $params
     * @return string
     * @throws RouterMatchException
     */
    public function getPathFilledIn(array $params): string
    {
        if ($this->hasWildcard()) {
            $uriParts = [];
            $parts = $this->getRouteParts();
            foreach ($parts as $part) {
                if ($part->isWildcard()) {
                    $value = $params[$part->getString()] ?? null;
                    if ($value === null) {
                        throw new RouterMatchException("Redirect expects parameter {$part->getString()}");
                    }
                    $uriParts[] = $value;
                } else {
                    $uriParts[] = $part->getString();
                }
            }
            $uri = implode('/', $uriParts);
        } else {
            $uri = $this->getPath();
        }

        return $uri;
    }
}
