<?php

declare(strict_types = 1);

namespace LoopingExec;

class TimeExecControl implements ExecControl
{
    private float $maxRunTime;

    private int $spinTimeInMilliseconds;

    private int $minimumTimeBetweenRunsInMilliseconds;

    private float $startTime;

    private float $lastRuntime;

    private int $setTimeLimitInSeconds;

    /**
     *
     * @param float $maxRunTime
     * @param int $minimumTimeBetweenRunsInMilliseconds
     * @param int $spinTimeInMilliseconds How many milliseconds to wait between checking to see whether
     *   the callable should be run this loop. A value of 0 will result in 100% CPU usage. 10ms seems to be about 10% cpu usage, and 50ms is about 2% usage. An acceptable value will depend on your use-case.
     * @param int $setTimeLimitInSeconds
     */
    public function __construct(
        float $maxRunTime,
        int $minimumTimeBetweenRunsInMilliseconds,
        int $spinTimeInMilliseconds,
        int $setTimeLimitInSeconds
    ) {
        $this->maxRunTime = $maxRunTime;
        $this->minimumTimeBetweenRunsInMilliseconds = $minimumTimeBetweenRunsInMilliseconds;
        $this->spinTimeInMilliseconds = $spinTimeInMilliseconds;
        $this->setTimeLimitInSeconds = $setTimeLimitInSeconds;
    }

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->lastRuntime = 0;
    }

    public function shouldEnd(): bool
    {
        if ((microtime(true) - $this->startTime) > $this->maxRunTime) {
            return true;
        }
        return false;
    }

    public function shouldRun(): bool
    {
        set_time_limit($this->setTimeLimitInSeconds);
        $timeSinceLastRun = microtime(true) - $this->lastRuntime;
        if (($timeSinceLastRun * 1000) > $this->minimumTimeBetweenRunsInMilliseconds) {
            return true;
        }

        if ($this->spinTimeInMilliseconds != 0) {
            usleep($this->spinTimeInMilliseconds * 1000);
        }

        return false;
    }

    public function wasRun(): void
    {
        $this->lastRuntime = microtime(true);
    }
}
