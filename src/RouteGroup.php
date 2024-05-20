<?php

namespace CodePhix\Router;

class RouteGroup  extends Dispatch
/*implements
    MiddlewareAwareInterface,
    RouteCollectionInterface,
    RouteConditionHandlerInterface,
    StrategyAwareInterface*/
{
    use RouterTrait;
    /*
    use MiddlewareAwareTrait;
    use RouteCollectionTrait;
    use RouteConditionHandlerTrait;
    use StrategyAwareTrait;
    */

    /**
     * @var callable
     */
    protected $callback;

    protected $collection;

    /**
     * @var string
     */
    protected $prefix;

    public function __construct(string $prefix, callable $callback, $collection)
    {
        $this->callback   = $callback;
        $this->collection = $collection;
        $this->prefix     = sprintf('/%s', ltrim($prefix, '/'));
    }

    public function __invoke(): void
    {
        ($this->callback)($this);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function map(string $method, string $path, $handler)
    {
        $path  = ($path === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($path, '/'));
        $route = $this->collection->map($method, $path, $handler);

        $route->setParentGroup($this);

        if ($host = $this->getHost()) {
            $route->setHost($host);
        }

        if ($scheme = $this->getScheme()) {
            $route->setScheme($scheme);
        }

        if ($port = $this->getPort()) {
            $route->setPort($port);
        }

        // if ($route->getStrategy() === null && $this->getStrategy() !== null) {
        //     $route->setStrategy($this->getStrategy());
        // }

        return $route;
    }
}
