<?php

namespace DataValidata\AsyncApp;

use Psr\Log\LoggerInterface;

class RequestLogger implements \Aerys\Bootable, \Aerys\Middleware, LoggerInterface
{
    /** @var  \Psr\Log\LoggerInterface */
    private $logger;

    public static $LOG_LEVEL = 'info';

    const LOG_FORMAT = '%s %s %s [%s] "%s %s HTTP/%s" %s %s';

    function boot(\Aerys\Server $server, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function do(\Aerys\InternalRequest $ireq)
    {
        $headers = yield;

        $this->logger->log(
            self::$LOG_LEVEL,
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

    public function emergency($message, array $context = array())
    {
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = array())
    {
        // TODO: Implement debug() method.
    }

    public function log($level, $message, array $context = array())
    {
        // TODO: Implement log() method.
    }
}