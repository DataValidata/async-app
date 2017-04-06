<?php

namespace DataValidata\AsyncApp\Framework;


use DataValidata\AsyncApp\Framework\Route\NamedAction;

class AppRoute
{
    private $path;
    private $method;
    private $action;
    private $name = null;

    private function __construct($path, $method, $action)
    {
        if(is_array($action)) {
            $action = forward_static_call_array([NamedAction::class, 'build'], $action);
        }
        $this->path = $path;
        $this->method = $method;
        $this->action = $action;
    }

    public static function get($path, $action)
    {
        return new static($path, 'get', $action);
    }

    public function withName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function path()
    {
        return $this->path;
    }

    public function method()
    {
        return $this->method;
    }

    public function action()
    {
        return $this->action;
    }

    public function isNamed()
    {
        return $this->name !== null;
    }

    public function name()
    {
        return $this->name;
    }
}