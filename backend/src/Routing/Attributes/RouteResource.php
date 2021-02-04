<?php
declare(strict_types=1);

namespace TestingTimes\Routing\Attributes;

use Attribute;

/**
 * Class ResourceController
 *
 * @package TestingTimes\Routing
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RouteResource
{
    /**
     * @param string $baseUri
     * @param string[] $methods
     */
    public function __construct(
        protected string $baseUri,
        protected array $methods = ['index', 'post', 'get', 'update', 'delete']
    ) {
    }

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @param string $baseUri
     * @return RouteResource
     */
    public function setBaseUri(string $baseUri): RouteResource
    {
        $this->baseUri = $baseUri;

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
     * @param array|string[] $methods
     * @return RouteResource
     */
    public function setMethods(array $methods): RouteResource
    {
        $this->methods = $methods;

        return $this;
    }
}
