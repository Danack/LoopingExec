<?php

declare(strict_types = 1);

namespace LoopingExec;

/**
 * Allows custom loop controls, as different applications can have very different
 * ideas about
 */
interface ExecControl
{
    /**
     * Called before the first loop. Allows for initialization of
     * any variables needed.
     */
    public function start(): void;

    /**
     * Called at end of every loop, to allow control over whether looping should end.
     *
     * @return bool true if the loop should exit, false if it should keep running.
     */
    public function shouldEnd(): bool;

    /**
     * Called every loop, to allow control over whether the callable should
     * be executed this loop.
     * @return bool true if the callable should be executed this loop.
     */
    public function shouldRun(): bool;

    /**
     * Called after the callable was executed. Allows for resetting PHP's set_time_limit.
     * Also, good place to add 'spin' delay to prevent CPU usage from being 100% for this worker.
     */
    public function wasRun(): void;
}
