<?php
declare(strict_types=1);

namespace TestingTimes\Routing;

use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use ReflectionException;
use TestingTimes\Routing\Attributes\Route;
use TestingTimes\Routing\Attributes\RouteResource;
use TestingTimes\Routing\Exceptions\RouterAddRouteException;

/**
 * The routeParser can parse defined routes into actual route objects and add it to the router
 */
class RouteParser
{
    /**
     * @var Router
     */
    protected Router $router;

    /**
     * @var string
     */
    protected string $resourceIdentifier;

    /**
     * @param  Router  $router
     * @param  string  $resourceIdentifier
     */
    public function __construct(Router $router, string $resourceIdentifier = 'id')
    {
        $this->router = $router;
        $this->resourceIdentifier = $resourceIdentifier;
    }

    /**
     * @param  string  $className
     * @return RouteParser
     * @throws ReflectionException
     */
    public function byClassMethods(string $className): self
    {
        $reflect = new ReflectionClass($className);

        foreach ($reflect->getMethods() as $method) {
            $attributes = $method->getAttributes(Route::class);

            foreach ($attributes as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();
                $route->setCallable([$className, $method->getName()]);
                $this->router->addRoute($route);
            }
        }

        return $this;
    }

    /**
     * @param  string  $className
     * @return $this
     * @throws ReflectionException
     * @throws RouterAddRouteException
     */
    public function byResource(string $className): self
    {
        $reflect = new ReflectionClass($className);
        $attributes = $reflect->getAttributes(RouteResource::class);

        foreach ($attributes as $attribute) {
            /** @var RouteResource $resource */
            $resource = $attribute->newInstance();
            $methods = $resource->getMethods();
            foreach ($methods as $method) {
                if (!method_exists($className, $method)) {
                    throw new RouterAddRouteException("method {$method}() for resource {$className}::{$method}() does not exist");
                }

                // add the route by reflection
                $path = $this->getPath($method, $resource->getBaseUri());

                $route = new Route($path, [$this->mapFunctionNamesToHttpMethods($method)]);
                $route->setCallable([$className, $method]);
                $this->router->addRoute($route);
            }
        }

        return $this;
    }

    /**
     * @param  string  $method
     * @param  string  $base
     * @return string
     */
    #[Pure] protected function getPath(string $method, string $base): string
    {
        return match ($method) {
            'get', 'show', 'detail', 'details', 'update', 'put', 'patch', 'delete', 'remove' => $base.'/{id}',
            'index', 'list', 'post', 'create' => $base,
            default => $base,
        };
    }

    /**
     * @param  string  $functionName
     * @return string
     * @throws RouterAddRouteException
     */
    #[Pure] protected function mapFunctionNamesToHttpMethods(string $functionName): string
    {
        return match (strtolower($functionName)) {
            'index', 'list', 'get', 'show', 'detail', 'details' => 'GET',
            'post', 'create', 'make', 'insert' => 'POST',
            'put', 'update' => 'PUT',
            'patch' => 'PATCH',
            'delete', 'remove' => 'DELETE',
            default => throw new RouterAddRouteException("Controller method with name {$functionName} is not allowed"),
        };
    }
}
