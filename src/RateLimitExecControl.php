<?php

declare(strict_types = 1);

namespace LoopingExec;

class RateLimitExecControl implements ExecControl
{
    private int $maxRunTime;
    private float $startTime;

    private int $spinTimeInMilliseconds;

    private int $runsPerTimePeriod;

    private int $timePeriod;

    private int $setTimeLimit;

    /**
     * A recording of the the when a run was completed
     * @var float[]
     */
    private array $runTimes = [];

    public function __construct(
        int $numberPerTimePeriod,
        int $timePeriod,
        int $maxRunTime,
        int $spinTimeInMilliseconds,
        int $setTimeLimitInSeconds
    ) {
        $this->maxRunTime = $maxRunTime;
        $this->spinTimeInMilliseconds = $spinTimeInMilliseconds;
        $this->runsPerTimePeriod = $numberPerTimePeriod;
        $this->timePeriod = $timePeriod;
        $this->setTimeLimit = $setTimeLimitInSeconds;
    }


    public function start(): void
    {
        $this->startTime = microtime(true);
    }

    public function shouldEnd(): bool
    {

        if ((microtime(true) - $this->startTime) > $this->maxRunTime) {
            return true;
        }
        return false;
    }

    private function discardOldRunTimes(): void
    {
        $currentTime = microtime(true);
        $newRunTimes = [];
        foreach ($this->runTimes as $runTime) {
            // Should that run still be considered?
            if (($runTime + $this->timePeriod) > $currentTime) {
                $newRunTimes[] = $runTime;
            }
        }
        $this->runTimes = $newRunTimes;
    }

    public function shouldRun(): bool
    {
        set_time_limit($this->setTimeLimit);
        $this->discardOldRunTimes();

        if (count($this->runTimes) < $this->runsPerTimePeriod) {
            return true;
        };

        if ($this->spinTimeInMilliseconds !== 0) {
            usleep($this->spinTimeInMilliseconds * 1000);
        }

        return false;
    }

    public function wasRun(): void
    {
        $this->runTimes[] = microtime(true);
    }
}
