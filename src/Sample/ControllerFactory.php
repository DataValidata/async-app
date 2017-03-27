<?php

namespace DataValidata\AsyncApp\Sample;


class ControllerFactory
{
    /** @var \DateTime  */
    private $dateTime;
    private $offset;

    public function __construct(\DateTime $dateTime, $offset)
    {
        $this->dateTime = $dateTime;
        $this->offset = $offset;
    }

    function __invoke()
    {
        return new Controller($this->dateTime, $this->offset);
    }
}