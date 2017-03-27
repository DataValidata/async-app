<?php

namespace DataValidata\AsyncApp;

class Logger implements \Aerys\Bootable, \Aerys\Middleware
{
    /** @var  \Psr\Log\LoggerInterface */
    private $logger;

    const LOG_FORMAT = '%s %s %s [%s] "%s %s HTTP/%s" %s %s';

    function boot(\Aerys\Server $server, \Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function do(\Aerys\InternalRequest $ireq)
    {
        $headers = yield;

        $this->logger->info(
            vsprintf(
                self::LOG_FORMAT,
                [
                    $ireq->client->clientAddr,
                    '-', // identifier
                    '-', // user
                    strftime('%d/%b/%Y:%H:%M:%S %z'),
                    $ireq->method,
                    $ireq->uri,
                    $ireq->protocol,
                    $headers[':status'],
                    '-', // size
                ]
            )
        );

        return $headers;
    }

}