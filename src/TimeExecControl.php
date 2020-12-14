<?php

declare(strict_types = 1);

namespace LoopingExec;

class TimeExecControl implements ExecControl
{
    private float $maxRunTime;

    private int $spinTime;

    private int $minimumTimeBetweenRunsInMilliseconds;

    private float $startTime;

    private float $lastRuntime;

    /**
     *
     * @param float $maxRunTime
     * @param int $minimumTimeBetweenRunsInMilliseconds
     * @param int $spinTime
     */
    public function __construct(
        float $maxRunTime,
        int $minimumTimeBetweenRunsInMilliseconds,
        int $spinTime
    ) {
        $this->maxRunTime = $maxRunTime;
        $this->spinTime = $spinTime;
        $this->minimumTimeBetweenRunsInMilliseconds = $minimumTimeBetweenRunsInMilliseconds;
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
        $timeSinceLastRun = microtime(true) - $this->lastRuntime;
        if (($timeSinceLastRun * 1000) > $this->minimumTimeBetweenRunsInMilliseconds) {
            return true;
        }
        return false;
    }

    public function wasRun(): void
    {
        $this->lastRuntime = microtime(true);

        if ($this->spinTime != 0) {
            usleep($this->spinTime);
        }
    }
}
