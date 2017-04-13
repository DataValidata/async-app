<?php


namespace DataValidata\AsyncApp;


use Aerys\Bootable;
use Aerys\Server;
use Psr\Log\LoggerInterface as PsrLogger;

class AerysInternals implements Bootable, PsrLogger
{
    /** @var  Server */
    private $server = null;

    /** @var  PsrLogger */
    private $logger = null;

    private $booted = false;

    private $logBuffer = [];

    function boot(Server $server, PsrLogger $logger)
    {
        $this->server = $server;
        $this->logger = $logger;
        $this->booted = true;
        while(($bufferedMessage = array_shift($this->logBuffer))) {
            call_user_func_array([$this, 'delegateLog'], $bufferedMessage);
        }
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return PsrLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    private function delegateLog(...$args)
    {
        if($this->isBooted()) {
            $logType = array_shift($args);
            call_user_func_array([$this->logger, $logType], $args);
        } else {
            $this->logBuffer[] = $args;
        }
    }

    public function emergency($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        if(!array_key_exists('pid', $context)) {
            $context['pid'] = getmypid();
        }
        $this->delegateLog(__FUNCTION__, $level, $message, $context);
    }
}