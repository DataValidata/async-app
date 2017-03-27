<?php

namespace DataValidata\AsyncApp;

use Auryn\Injector;

interface InjectionVisitable
{
    public function receiveInjectionVisit(Injector $injector);
}