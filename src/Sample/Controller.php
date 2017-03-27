<?php

namespace DataValidata\AsyncApp\Sample;


class Controller
{
    /** @var \DateTime  */
    private $created;
    private $offset;

    public function __construct(\DateTime $created, $offset)
    {
        $this->created = $created;
        $this->offset = $offset;
    }

    public function __invoke(\Aerys\Request $req, \Aerys\Response $res, $routeArgs = [])
    {
        $sinceCreated = ((new \DateTime)->getTimestamp() - $this->created->getTimestamp()) + $this->offset;

        $res->end("<html><body><h1>AppDefault/Controller : $sinceCreated</h1></body></html>");
    }
}