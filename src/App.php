<?php

namespace DataValidata\AsyncApp;

use Functional;
use Auryn\Injector;
use Aerys\{Host, Router};
use Dotenv\Dotenv;

final class App implements AsynchronousApp
{
    /** @var Host */
    private $host;

    /** @var Injector  */
    private $injector;

    /** @var Router  */
    private $router;

    private $hostUsables = [];

    public final function __construct(Injector $injector)
    {
        $this->injector = $injector;
        $this->attachHostUsable($this->router = \Aerys\router());

        $this->loadEnvironment();
        $this->validateEnvironment();
        $this->listen();

        $this->loadServices();
    }

    private function loadServices()
    {
        Functional\map(
            ServiceLoader::getInstance()->getServices(),
            $this->getServiceBuildChain()
        );

        $fallback = function(\Aerys\Request $req, \Aerys\Response $res) {
            $res->end("<html><body><h1>Fallback \o/</h1></body></html>");
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
                $routes = $service->getRouteConfiguration();
                $serviceRouter = \Aerys\router();
                foreach($routes['routes'] as $route => $detail) {
                    foreach($detail as $method => $controllerSpec) {
                        if(is_callable($controllerSpec)) {
                            $controller = $controllerSpec;
                        } else {
                            $this->injector->share($controllerSpec);
                            $controller = $this->injector->make($controllerSpec);
                        }

                        $serviceRouter->route(strtoupper($method), $route, $controller);
                    }
                }
                $serviceRouter->prefix($routes['prefix']);
                $this->router->use($serviceRouter);
            }

            if($service instanceof ExposesStaticRouting) {
                $docRoots = $service->getDocRoots();
                foreach($docRoots as $docRoot) {
                    $this->attachHostUsable(\Aerys\root($docRoot));
                }
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
        if (file_exists(dirname(__DIR__) . '/.env')) {
            $dotenv = new Dotenv(dirname(__DIR__));
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
        $this->host = (new Host())->expose("*", getenv('PORT'))
            ->use($this->injector->make(Logger::class));
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
}