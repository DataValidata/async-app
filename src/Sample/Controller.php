<?php

namespace DataValidata\AsyncApp\Sample;


use DataValidata\AsyncApp\Http\Routes\Generator;

class Controller
{
    /** @var Generator  */
    private $generator;

    public function __construct(Generator $routeGenerator)
    {
        $this->generator = $routeGenerator;
    }

    public function __invoke(\Aerys\Request $req, \Aerys\Response $res, $routeArgs = [])
    {
        $route = $this->generator->generate('index', [], ['id'=>'some', 'address'=>'102 tonlegee road']);

        $res->end("<html><body><h1>AppDefault/Controller : $route</h1></body></html>");
    }
}