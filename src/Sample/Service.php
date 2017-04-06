<?php

namespace DataValidata\AsyncApp\Sample;

use DataValidata\AsyncApp\ExposesRouting;
use DataValidata\AsyncApp\Framework\AppRoute;
use DataValidata\AsyncApp\Framework\RouteConfiguration;
use DataValidata\AsyncApp\InjectionVisitable;
use Auryn\Injector;

class Service implements ExposesRouting, InjectionVisitable
{
    public function receiveInjectionVisit(Injector $injector)
    {
//        $injector
//            ->define(ControllerFactory::class, [
//                ':dateTime' => (new \DateTime),
//                ':offset' => 200
//            ])
//            ->share(ControllerFactory::class)
//            ->delegate(Controller::class, ControllerFactory::class)
//        ;
    }

    public function getRouteConfiguration(): RouteConfiguration
    {
        return RouteConfiguration::withRoutes([
            AppRoute::get('/', Controller::class),
            AppRoute::get('/named', [Controller::class, 'namedAction'])->withName('namedAction'),
        ]);
    }
}