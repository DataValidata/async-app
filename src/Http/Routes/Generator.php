<?php

namespace DataValidata\AsyncApp\Http\Routes;


use Aura\Router\RouterContainer;

class Generator
{
    /** @var  \Aura\Router\Generator */
    private $auraGenerator;

    public function __construct(RouterContainer $routes)
    {
        $this->auraGenerator = $routes->getGenerator();
    }

    public function generate($name, $urlData = [], $queryData = [])
    {
        $url = rtrim($this->auraGenerator->generate($name, $urlData), "/");

        if(!empty($queryData)) {
            $url .= '?' . http_build_query($queryData);
        }

        return '/' . ltrim($url, "/");
    }
}