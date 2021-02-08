<?php
declare(strict_types=1);

namespace TestingTimes;

use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Composer\Autoload\ClassMapGenerator;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;
use Exception;
use HanWoolderink88\Container\Container;
use HanWoolderink88\Container\Exception\ContainerAddServiceException;
use JsonException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use TestingTimes\App\Controllers\IndexController;
use TestingTimes\App\Controllers\OrderController;
use TestingTimes\App\Controllers\PostController;
use TestingTimes\App\Controllers\ProductController;
use TestingTimes\App\Controllers\UserController;
use TestingTimes\Config\Config;
use TestingTimes\Config\Env;
use TestingTimes\ErrorHandling\ErrorHandler;
use TestingTimes\Http\Contracts\RequestContract;
use TestingTimes\Http\Response\JsonResponse;
use TestingTimes\Routing\RouteMatcher;
use TestingTimes\Routing\RouteParser;
use TestingTimes\Routing\Router;
use Throwable;

/**
 * Class Kernel
 *
 * @package TestingTimes
 */
class Kernel implements RequestHandlerInterface
{
    protected ContainerInterface $container;

    /**
     * Should go to its own class
     *
     * @throws ContainerAddServiceException
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws ReflectionException
     * @throws Routing\Exceptions\RouterAddRouteException
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function bootstrap()
    {
        $cachePool = $this->loadCache();
        $useCache = false; //$_SERVER['APP_CACHE_ENABLED'] === 'true';

        if ($useCache && $cachePool->hasItem('bootstrap')) {
            $this->loadDotEnv($cachePool, $useCache);

            /** @var ContainerInterface $container */
            $container = $cachePool->get('bootstrap');
            $this->container = $container;
        } else {
            $this->loadDotEnv($cachePool, $useCache);

            $this->container = new Container();

            $this->container->addService($this->loadEnv());
            $this->container->addService($this->loadConfig());
            $this->container->addService($this->loadDbal());
            $this->container->addService($this->loadOrm());

            $routeMatcher = new RouteMatcher($this->loadRouter());
            $routeMatcher->setContainer($this->container);
            $this->container->addService($routeMatcher);

            $this->autoWireContainer();

            $this->container->sortIndex();

            if ($useCache) {
                $cachePool->save($cachePool->getItem('bootstrap')->set($this->container));
            }
        }

        $this->container->addService($cachePool);
    }

    /**
     * @return AbstractCachePool
     */
    protected function loadCache(): AbstractCachePool
    {
        $fileLocation = dirname(__DIR__) . '/config/cache.php';
        if (is_file($fileLocation)) {
            $config = include $fileLocation;
            $driver = $config['driver'] ?? 'filesystem';
        } else {
            $driver = 'filesystem';
        }

        switch ($driver) {
            case 'apcu':
                return new ApcuCachePool();
            case 'filesystem':
            default:
                $filesystemAdapter = new Local(dirname(__DIR__) . '/storage/');
                $filesystem = new Filesystem($filesystemAdapter);

                return new FilesystemCachePool($filesystem);
        }
    }

    /**
     * @param AbstractCachePool $cachePool
     * @param bool $useCache
     * @return array|null
     * @throws InvalidArgumentException
     */
    protected function loadDotEnv(AbstractCachePool $cachePool, bool $useCache): ?array
    {
        // load the dotenv vars from cache
        if ($useCache) {
            $vars = $cachePool->get('envvars');
            if ($vars) {
                // todo
                foreach ($vars as $key => $value) {
                    $_SERVER[$key] = $value;
                    $_ENV[$key] = $value;
                }
            }
        }

        // load from file
        if (!$useCache || !isset($vars)) {
            if (file_exists(dirname(__DIR__) . '/.env')) {
                $dotenv = Dotenv::createImmutable(dirname(__DIR__));
                $vars = $dotenv->load();

                $cachePoolItem = $cachePool->getItem('envvars')->set($vars);
                $cachePool->save($cachePoolItem);

                return $vars;
            }
        }

        return null;
    }

    /**
     * @return Env
     */
    protected function LoadEnv(): Env
    {
        return new Env();
    }

    /**
     * @return Config
     * @throws Exception
     */
    protected function loadConfig(): Config
    {
        $env = $this->container->get(Env::class);

        return new Config($env);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\Exception
     */
    private function loadDbal()
    {
        /** @var Config $config */
        $config = $this->container->get(Config::class);
        $connection = ['url' => $config->get('doctrine.url')];

        return DriverManager::getConnection($connection);
    }

    /**
     * @return EntityManager
     * @throws ORMException
     */
    protected function loadOrm(): EntityManager
    {
        $env = $this->getContainer()->get(Env::class);
        $config = $this->container->get(Config::class);

        if ($env->get('APP_CACHE_ENABLED')) {
            $cache = match (strtolower($config->get('cache.driver'))) {
                'apcu' => new ApcuCache(),
                default => new FilesystemCache(dirname(__DIR__) . '/storage/cache'),
            };
        } else {
            $cache = null;
        }

        $emConfig = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/App/Entities"],
            $config->get('doctrine.dev_mode'),
            $config->get('doctrine.proxy_dir'),
            $cache,
            $config->get('doctrine.use_simple_annotation_reader'));

        $connection = ['url' => $config->get('doctrine.url')];

        return EntityManager::create($connection, $emConfig);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Todo: this should be a file looup based on defined dirs in config a config file
     *
     * @return Router
     * @throws Routing\Exceptions\RouterAddRouteException
     * @throws ReflectionException
     */
    protected function loadRouter(): Router
    {
        $router = new Router();
        $router->flushRoutes();
        $routeParser = new RouteParser($router);
        $routeParser->byClassMethods(IndexController::class)
            ->byClassMethods(OrderController::class)
            ->byResource(UserController::class)
            ->byClassMethods(PostController::class)
            ->byClassMethods(ProductController::class);

        return $router;
    }

    /**
     * @return void
     */
    protected function autoWireContainer()
    {
        /** @var Config $config */
        $config = $this->container->get(Config::class);
        $directories = $config->get('container.autowire', []);

        foreach ($directories as $directory) {
            $map = ClassMapGenerator::createMap($directory);

            foreach ($map as $className => $location) {
                $this->container->addServiceReference($className);
            }
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws JsonException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->addService($request, [RequestContract::class]);

        try {
            $routeMatcher = $this->container->get(RouteMatcher::class);

            return $routeMatcher->handle($request);
        } catch (Throwable $throwable) {
            /** @var ErrorHandler $handler */
            $handler = $this->container->get(ErrorHandler::class);

            // todo: second param should be dynamic based on config i guess...
            return $handler->handle($throwable, new JsonResponse(''));
        }
    }
}
