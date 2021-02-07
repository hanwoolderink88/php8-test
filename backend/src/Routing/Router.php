<?php
declare(strict_types=1);

namespace TestingTimes\Routing;

use JetBrains\PhpStorm\Pure;
use TestingTimes\Routing\Attributes\Route;

/**
 * The router handles the actual routes from adding, retrieving and storing
 */
class Router
{
    /**
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param  Route[]  $routes
     * @return Router
     */
    public function setRoutes(array $routes): Router
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * @param  Route  $route
     * @return Router
     */
    public function addRoute(Route $route): Router
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @param  Route  $route
     * @return $this
     */
    public function removeRoute(Route $route): Router
    {
        // todo: use collections instead of plain arrays

        return $this;
    }

    /**
     * @return $this
     */
    public function flushRoutes(): self
    {
        $this->routes = [];

        return $this;
    }

    /**
     * @param  string  $routePath
     * @return Route|null
     */
    #[Pure] public function getRouteByPath(string $routePath): ?Route
    {
        foreach ($this->routes as $route) {
            if ($routePath === $route->getPath()) {
                return $route;
            }
        }

        return null;
    }
}
