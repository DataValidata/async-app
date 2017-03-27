<?php

namespace DataValidata\AsyncApp\Sample;

use DataValidata\AsyncApp\ExposesRouting;
use DataValidata\AsyncApp\InjectionVisitable;
use Auryn\Injector;
use DataValidata\AsyncApp\Sample\Controller;

class Service implements ExposesRouting, InjectionVisitable
{
    public function receiveInjectionVisit(Injector $injector)
    {
        $injector
            ->define(ControllerFactory::class, [
                ':dateTime' => (new \DateTime),
                ':offset' => 200
            ])
            ->share(ControllerFactory::class)
            ->delegate(Controller::class, ControllerFactory::class)
        ;
    }

    public function getRouteConfiguration()
    {
        return [
            'prefix' => '',
            'routes' => [
                'index' => [
                    'path' => '/',
                    'method' => 'get',
                    'action' => Controller::class,
                ]
            ]
        ];
    }
}