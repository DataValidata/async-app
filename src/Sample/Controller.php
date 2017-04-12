<?php

namespace DataValidata\AsyncApp\Sample;


use DataValidata\AsyncApp\Http\Routes\Generator;
use Psr\Log\LoggerInterface;

class Controller
{
    /** @var Generator  */
    private $generator;

    /** @var LoggerInterface  */
    private $logger;
    public function __construct(Generator $routeGenerator, LoggerInterface $logger)
    {
        $this->generator = $routeGenerator;
        $logger->critical("LOGGER IS INJECTED!");
        $this->logger = $logger;
    }

    public function __invoke(\Aerys\Request $req, \Aerys\Response $res, $routeArgs = [])
    {
        $this->logger->warning("Request handle");
        $route = $this->generator->generate('namedAction', [], ['id'=>'some', 'address'=>'54 some street']);

        $res->end("<html><body><h1>AppDefault/Controller</h1><a href='$route'>namedAction</a> </body></html>");
    }

    public function namedAction(\Aerys\Request $req, \Aerys\Response $res, $routeArgs = [])
    {
        $res->end("<html><body><h1>AppDefault/Controller(namedAction)</h1></body></html>");
    }
}