<?php

namespace DataValidata\AsyncApp\Framework;


use Assert\Assertion;
use Traversable;

class RouteConfiguration implements \IteratorAggregate
{
    private $prefix = '';

    /** @var  AppRoute[] */
    private $routes;

    private function __construct($routes, $prefix = '')
    {
        $this->prefix = $prefix;
        $this->routes = $routes;
    }

    public static function withRoutes($routes, $prefix ='')
    {
        Assertion::allIsInstanceOf($routes, AppRoute::class, 'All routes must be AppRoutes');
        return new static($routes, $prefix);
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function getIterator()
    {
        foreach ($this->routes as $index => $route) {
            if($route->isNamed()) {
                yield $route->name() => $route;
            } else {
                yield $index => $route;
            }
        }
    }
}