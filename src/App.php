<?php

namespace DataValidata\AsyncApp;

use Aura\Router\{RouterContainer, Route};
use DataValidata\AsyncApp\Framework\AppRoute;
use DataValidata\AsyncApp\Framework\Route\NamedAction;
use DataValidata\AsyncApp\Framework\RouteConfiguration;
use DataValidata\AsyncApp\Http\Routes\Generator;
use Functional;
use Auryn\Injector;
use Aerys\{Host, Router};
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;

final class App implements AsynchronousApp
{
    /** @var Host */
    private $host;

    /** @var Injector  */
    private $injector;

    /** @var Router  */
    private $router;

    /** @var  RouterContainer */
    private $routes;

    private $hostUsables = [];

    public final function __construct(Injector $injector)
    {
        $this->injector = $injector;
        $this->injector->share(AerysInternals::class)->make(AerysInternals::class);
        $this->injector->delegate(LoggerInterface::class, function() {
            /** @var AerysInternals $internals */
            $internals = $this->injector->make(AerysInternals::class);
            if($internals->isBooted()) {
                return $internals->getLogger();
            } else {
                return $internals;
            }
        });
        $this->attachHostUsable($this->router = \Aerys\router());

        $this->loadEnvironment();
        $this->validateEnvironment();
        $this->listen();

        $this->loadServices();
    }

    private function loadServices()
    {
        $this->routes = $this->injector->share(RouterContainer::class)->make(RouterContainer::class);
        $this->injector->share(Generator::class);
        Functional\map(
            ServiceLoader::getInstance()->getServices(),
            $this->getServiceBuildChain()
        );

        $fallback = function(\Aerys\Request $req, \Aerys\Response $res) {

            $list = "";
            foreach($this->routes->getMap() as $route) {
                /** @var Route $route */
                $url = '/' . ltrim($route->path, "/");
                $list .= "<li>$url</li>";
            }

            $res->end("<html><body><h1>Fallback \o/</h1><ul>$list</ul></body></html>");
        };
        $this->attachHostUsable($fallback);

        foreach($this->hostUsables as $usable) {
            $this->host->use($usable);
        }
    }

    private function getServiceBuildChain()
    {
        $wrap = function($callable) {
            return function($serviceName) use ($callable) {
                $callable($serviceName);
                return $serviceName;
            };
        };

        $buildService = function ($serviceName) {
            $this->injector->share($serviceName)->make($serviceName);
        };

        $handleInjectionVisits = function ($serviceName) {
            $service = $this->injector->make($serviceName);
            $class = new \ReflectionClass($service);
            if($class->implementsInterface(InjectionVisitable::class)) {
                call_user_func([$service, 'receiveInjectionVisit'], $this->injector);
            }
        };

        $initialiseRouting = function($serviceName) {
            $service = $this->injector->make($serviceName);
            if($service instanceof ExposesRouting) {
                $this->extractRouting($service);
            }

            if($service instanceof ExposesStaticRouting) {
                $this->extractStaticRouting($service);
            }
        };

        return call_user_func_array(
            'Functional\compose',
            Functional\map(
                [
                    $buildService,
                    $handleInjectionVisits,
                    $initialiseRouting
                ],
                $wrap
            )
        );
    }

    private function loadEnvironment()
    {
        $appRoot = getenv('APP_ROOT');
        if (file_exists($appRoot . '/.env')) {
            $dotenv = new Dotenv($appRoot);
            $dotenv->load();
        }
    }

    private function validateEnvironment()
    {
        \Assert\Assertion::between(
            getenv('PORT'),
            1, 65535,
            "Invalid port number; integer in the range 1..65535 required"
        );
    }

    /**
     * @return \Aerys\Host
     */
    private function listen()
    {
        $this->host = (new Host())
            ->expose("*", getenv('PORT'))
            ->use($this->injector->make(Logger::class))
            ->use($this->injector->make(AerysInternals::class))
        ;
        $this->injector->share($this->host);
        return $this->host;
    }

    /**
     * @param $usable
     */
    private function attachHostUsable($usable)
    {
        $this->hostUsables[] = $usable;
    }

    private function extractRouting(ExposesRouting $service)
    {
        /** @var RouteConfiguration|AppRoute[] $routes */
        $routes = $service->getRouteConfiguration();
        $serviceRouter = \Aerys\router();
        foreach($routes as $routeDetail) {
            $path   = $routeDetail->path();
            $method = $routeDetail->method();
            $action = $routeDetail->action();

            if(is_callable($action)) {
                $controller = $action;
            } else {
                if($action instanceof NamedAction) {
                    $controller = $action->wrap($this->injector);
                } else {
                    $this->injector->share($action);
                    $controller = $this->injector->make($action);
                }
            }

            if($routeDetail->isNamed()) {
                ($this->routes->getMap())->addRoute(
                    (new Route())
                        ->name($routeDetail->name())
                        ->pathPrefix($routes->prefix())
                        ->path($path)
                );
            }

            $serviceRouter->route(strtoupper($method), $path, $controller);
        }
        $serviceRouter->prefix($routes->prefix());
        $this->router->use($serviceRouter);
    }

    private function extractStaticRouting(ExposesStaticRouting $service)
    {
        $docRoots = $service->getDocRoots();
        foreach($docRoots as $docRoot) {
            $this->attachHostUsable(\Aerys\root($docRoot));
        }
    }
}