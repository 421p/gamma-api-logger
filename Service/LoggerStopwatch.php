<?php

namespace Gamma\ApiLoggerBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Wrapper for basic Symfony's Stopwatch service
 * It add logging functionality
 */
class LoggerStopwatch implements LoggerStopwatchInterface
{
    const EVENT_PREFIX = '';

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $stopwatchEnabled;

    /**
     * @param Stopwatch       $stopwatch
     * @param LoggerInterface $logger
     * @param bool            $stopwatchEnabled
     */
    public function __construct(Stopwatch $stopwatch, LoggerInterface $logger, $stopwatchEnabled = false) 
    {
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
        $this->stopwatchEnabled = $stopwatchEnabled;
    }

    /**
     * @see Stopwatch::start
     * @param string $eventName
     * @return false|StopwatchEvent
     */
    public function start($eventName)
    {
        return ($this->stopwatchEnabled) ? $this->stopwatch->start(static::EVENT_PREFIX.$eventName) : false;
    }

    /**
     * @see Stopwatch::stop
     * @param string $eventName
     * @param array  $extraParams
     * @return false|StopwatchEvent
     */
    public function stop($eventName, array $extraParams = array())
    {
        if (! $this->stopwatchEnabled) {
            return false;
        }
        $event = $this->stopwatch->stop(static::EVENT_PREFIX.$eventName);
        $this->logEvent($event, static::EVENT_PREFIX.$eventName, $extraParams);
        return $event;
    }

    /**
     * Save event data to the log
     *
     * @param StopwatchEvent $event
     * @param string         $eventName
     * @param array          $params
     */
    protected function logEvent(StopwatchEvent $event, $eventName, array $params)
    {
        $this->logger->info($eventName . ',                ' . $event->getDuration().'ms', $params);
    }
}
