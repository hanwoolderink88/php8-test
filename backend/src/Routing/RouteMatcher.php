<?php
declare(strict_types=1);

namespace TestingTimes\Routing;

use JetBrains\PhpStorm\Pure;
use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use ReflectionMethod;
use TestingTimes\Routing\Attributes\Route;
use TestingTimes\Routing\Exceptions\RouterMatchException;

/**
 * The route matcher finds the best match in a collection of routes
 */
class RouteMatcher implements RequestHandlerInterface
{
    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container = null;

    /**
     * @param Router $router
     */
    public function __construct(protected Router $router)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ReflectionException
     * @throws RouterMatchException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // standardise path
        $path = rtrim(ltrim($request->getUri()->getPath(), '/'), '/');
        $pathParts = explode('/', $path);

        // Find a matching route
        $route = $this->findMatch($path, $pathParts, $request->getMethod());

        // return a 404 if the route is not found
        if ($route === null) {
            return $this->getResponse404();
        }

        return $this->callCallback($route, $pathParts);
    }

    /**
     * todo: should be definable
     *
     * @return ResponseInterface|null
     */
    public function getResponse404(): ?ResponseInterface
    {
        return new Response(404, [], 'not found');
    }

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container ?? null;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @param string $routePath
     * @param string[] $params
     * @param bool $permanent
     * @return ResponseInterface
     * @throws ReflectionException
     * @throws RouterMatchException
     */
    public function redirect(string $routePath, array $params = [], bool $permanent = false): ResponseInterface
    {
        $route = $this->router->getRouteByPath($routePath);

        if ($route === null) {
            throw new RouterMatchException('No route found to redirect to with name ' . $routePath);
        }

        $http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $route->getPathFilledIn($params);

        header("Location: {$http}://{$host}/{$uri}", true, $permanent ? 301 : 302);

        return ($this->callCallback($route, explode('/', $uri)))->withStatus($permanent ? 301 : 302);
    }

    /**
     * @param Route $route
     * @param string[] $pathParts
     * @return ResponseInterface
     * @throws ReflectionException
     * @throws RouterMatchException
     */
    protected function callCallback(Route $route, array $pathParts): ResponseInterface
    {
        // Param matching for wildcards and DI
        $params = $this->matchParams($route, $pathParts);
        $callable = null;

        // callable could be DI
        if ($this->getContainer() !== null && is_array($route->getCallable()) && is_string($route->getCallable()[0])) {
            $obj = $this->getContainer()->get($route->getCallable()[0]);
            if ($obj !== null) {
                $method = $route->getCallable()[1];
                $callable = [$obj, $method];
            }
        }

        // if no Di object is found try and create an object
        if ($callable === null) {
            if (is_array($route->getCallable()) && is_string($route->getCallable()[0])) {
                $className = $route->getCallable()[0];
                $callable = [new $className(), $route->getCallable()[1]];
            } else {
                $callable = $route->getCallable();
            }
        }

        // call the callback function of the matched route with the
        return call_user_func_array($callable, $params);
    }

    /**
     * @param string $path
     * @param string[] $pathParts
     * @param string $method
     * @return Route|null
     */
    #[Pure] protected function findMatch(string $path, array $pathParts, string $method): ?Route
    {
        $routes = $this->router->getRoutes();
        $match = $this->findDirectMatch($path, $method, $routes);
        if ($match === null) {
            $match = $this->findWildcardMatch($pathParts, $method, $routes);
        }

        return $match;
    }

    /**
     * @param string $path
     * @param string $method
     * @param Route[] $routes
     * @return Route|null
     */
    #[Pure] protected function findDirectMatch(string $path, string $method, array $routes): ?Route
    {
        foreach ($routes as $route) {
            if ($route->getPath() === $path && in_array($method, $route->getMethods(), true)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @param string[] $pathParts
     * @param string $method
     * @param Route[] $routes
     * @return Route|null
     */
    #[Pure] protected function findWildcardMatch(array $pathParts, string $method, array $routes): ?Route
    {
        $pathPartCount = count($pathParts);
        foreach ($routes as $route) {
            if ($route->hasWildcard() && in_array($method, $route->getMethods(), true)) {
                $parts = $route->getRouteParts();
                $partsCount = count($parts);

                // if the number of parts in both uri's is not the same it cannot be a match
                if ($partsCount !== $pathPartCount) {
                    continue;
                }

                // if all non wildcard parts match we have match
                if ($this->uriPartsMatch($pathParts, $parts)) {
                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * @param string[] $pathParts
     * @param RoutePart[] $parts
     * @return bool
     */
    #[Pure] protected function uriPartsMatch(array $pathParts, array $parts): bool
    {
        $i = 0;
        foreach ($pathParts as $pathPart) {
            $part = $parts[$i] ?? null;
            $i++;

            // if the part is a wildcard it does not have to match
            if ($part->isWildcard()) {
                continue;
            }

            // if the parts mismatch we do not have a match
            if ($part->getString() !== $pathPart) {
                return false;
            }
        }

        // if all is good we have a match
        return true;
    }

    /**
     * @param Route $route
     * @param string[] $pathParts
     * @return mixed[]
     * @throws ReflectionException
     * @throws RouterMatchException
     */
    protected function matchParams(Route $route, array $pathParts): array
    {
        $params = $this->getFunctionParams($route);
        $wildcards = $route->hasWildcard() ? $this->getWildcardValues($pathParts, $route->getRouteParts()) : [];

        $p = [];
        foreach ($params as $param) {
            $isWildcardParam = ($param['type'] === 'string' || $param['type'] === null);
            $isNullable = $param['nullable'];
            $value = null;

            if ($isWildcardParam) {
                // a wildcard param is defined in the route path by /{name}
                $value = $wildcards[$param['name']] ?? null;
            } else if ($this->container !== null && $this->container->has($param['type'])) {
                // (johnny) Dep inject
                $value = $this->container->get($param['type']);
            }

            if ($value === null && $isNullable === false) {
                $name = $param['name'];
                $msg = "Callback function has argument with name \"{$name}\" but no wildcard or DI service was found";
                throw new RouterMatchException($msg);
            }

            $p[] = $value;
        }

        return $p;
    }

    /**
     * @param Route $route
     * @return mixed[][]
     * @throws ReflectionException
     * @throws RouterMatchException
     */
    protected function getFunctionParams(Route $route): array
    {
        $callable = $route->getCallable();
        if (is_array($callable)) {
            $reflect = new ReflectionMethod($callable[0], $callable[1]);
        } else {
            throw new RouterMatchException('callable should be an array');
        }

        $fParams = $reflect->getParameters();

        $params = [];
        foreach ($fParams as $fParam) {
            $params[] = [
                'name' => $fParam->getName(),
                'type' => $fParam->getType() ? $fParam->getType()?->getName() : null,
                'nullable' => $fParam->allowsNull(),
            ];
        }

        return $params;
    }

    /**
     * @param string[] $pathParts
     * @param RoutePart[] $routeParts
     * @return string[]
     */
    #[Pure] protected function getWildcardValues(array $pathParts, array $routeParts): array
    {
        $wildcards = [];
        $i = 0;
        foreach ($routeParts as $part) {
            if ($part->isWildcard()) {
                $wildcards[$part->getString()] = $pathParts[$i];
            }
            $i++;
        }

        return $wildcards;
    }
}
