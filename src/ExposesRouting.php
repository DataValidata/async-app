<?php


namespace DataValidata\AsyncApp;


use DataValidata\AsyncApp\Framework\RouteConfiguration;

interface ExposesRouting
{
    public function getRouteConfiguration() : RouteConfiguration;
}