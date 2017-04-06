<?php

namespace DataValidata\AsyncApp\Framework\Route;


use Aerys\Request;
use Aerys\Response;
use Assert\Assertion;
use Auryn\Injector;
use function Functional\true;

class NamedAction
{
    private $class;
    private $method;

    private function __construct($class, $method)
    {
        $this->class = $class;
        $this->method = $method;
    }

    public static function build($className, $methodName)
    {
        $reflectionClass = new \ReflectionClass($className);
        Assertion::true($reflectionClass->hasMethod($methodName));
        $methodParameters = ($reflectionClass->getMethod($methodName))->getParameters();

        Assertion::between(count($methodParameters), 2, 3);

        Assertion::true($methodParameters[0]->getType() == Request::class);
        Assertion::true($methodParameters[1]->getType() == Response::class);

        return new static($className, $methodName);
    }

    /**
     * @param Injector $injector
     * @return \Callable
     */
    public function wrap(Injector $injector)
    {
        $injector->share($this->className());
        $obj = $injector->make($this->className());
        return function(Request $req, Response $resp, $routeArgs = []) use ($obj) {
            return call_user_func([$obj, $this->method()], $req, $resp, $routeArgs);
        };
    }

    public function className()
    {
        return $this->class;
    }

    public function method()
    {
        return $this->method;
    }
}