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
        $route = $this->generator->generate('namedAction', [], ['id'=>'some', 'address'=>'54 some street']);

        $res->end("<html><body><h1>AppDefault/Controller</h1><a href='$route'>namedAction</a> </body></html>");
    }

    public function namedAction(\Aerys\Request $req, \Aerys\Response $res, $routeArgs = [])
    {
        $res->end("<html><body><h1>AppDefault/Controller(namedAction)</h1></body></html>");
    }
}