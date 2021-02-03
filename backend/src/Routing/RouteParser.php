<?php
declare(strict_types=1);

namespace TestingTimes\Routing;

use ReflectionClass;
use ReflectionException;
use TestingTimes\Routing\Attributes\Route;
use TestingTimes\Routing\Attributes\RouteResource;

/**
 * The routeParser can parse defined routes into actual route objects and add it to the router
 */
class RouteParser
{
    /** @var Router */
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $className
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
                    throw new \Exception("method {$method}() for resource {$className}::{$method}() does not exist");
                }

                // add the route by reflection
                $base = $resource->getBaseUri();
                switch ($method) {
                    case 'index':
                    case 'list':
                    case 'post':
                    case 'create':
                        $path = $base;
                        break;
                    case 'get':
                    case 'show':
                    case 'detail':
                    case 'details':
                    case 'update':
                    case 'put':
                    case 'patch':
                    case 'delete':
                    case 'remove':
                        $path = $base . '/{id}';
                        break;
                    default:
                        $path = $base;
                }

                $route = new Route($path, [$this->mapFunctionNamesToHttpMethods($method)]);
                $route->setCallable([$className, $method]);
                $this->router->addRoute($route);
            }
        }

        return $this;
    }

    protected function mapFunctionNamesToHttpMethods(string $functionName)
    {
        $map = [
            // all get list
            'index' => 'GET',
            'list' => 'GET',
            // all get single
            'get' => 'GET',
            'show' => 'GET',
            'detail' => 'GET',
            'details' => 'GET',
            // all post
            'post' => 'POST',
            'create' => 'POST',
            'make' => 'POST',
            'insert' => 'POST',
            // all put/patch
            'put' => 'PUT',
            'patch' => 'PATCH',
            'update' => 'PUT',
            // all delete
            'delete' => 'DELETE',
            'remove' => 'DELETE',
        ];

        $var = $map[strtolower($functionName)] ?? null;

        return strtoupper($var);
    }
}
