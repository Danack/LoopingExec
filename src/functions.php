<?php

namespace LoopingExec;

/**
 * Self-contained monitoring system for system signals
 * returns true if a 'graceful exit' like signal is received.
 *
 * We don't listen for SIGKILL as that needs to be an immediate exit,
 * which PHP already provides.
 * @return bool
 */
function checkSignalsForExit()
{
    static $initialised = false;
    static $needToExit = false;
    static $fnSignalHandler = null;

    if ($initialised === false) {
        $fnSignalHandler = function ($signalNumber) use (&$needToExit) {
            $needToExit = true;
        };
        pcntl_signal(SIGINT, $fnSignalHandler, false);
        pcntl_signal(SIGQUIT, $fnSignalHandler, false);
        pcntl_signal(SIGTERM, $fnSignalHandler, false);
        pcntl_signal(SIGHUP, $fnSignalHandler, false);
        pcntl_signal(SIGUSR1, $fnSignalHandler, false);
        $initialised = true;
    }

    pcntl_signal_dispatch();

    return $needToExit;
}

/**
 * Prints a message to the output.
 *
 * aka why is print a construct?
 * @param string $message
 */
function echoLogger(string $message)
{
    echo $message = "\n";
}

/**
 * @param string $message
 */
function nullLogger(string $message)
{
}

/**
 * Repeatedly calls a callable until it's time to stop
 *
 * @param callable $callable - the thing to run
 * @param int $secondsBetweenRuns - the minimum time between runs in milliseconds
 * @param int $sleepTime - the time to sleep between runs in milliseconds
 * @param int $maxRunTime - the max time to run for, before returning in seconds
 */
function continuallyExecuteCallable(
    $callable,
    int $minimumTimeBetweenRuns,
    int $sleepTimeBetweenRuns,
    int $maxRunTime
) {
    continuallyExecuteCallableEx(
        $callable,
        $maxRunTime,
        $minimumTimeBetweenRuns,
        $sleepTimeBetweenRuns,
        50,
        'LoopingExec\nullLogger'
    );
}


/**
 * Repeatedly calls a callable until it's time to stop
 *
 * @param callable $callable - the thing to run
 * @param int $maxRunTime - the max time to run for, before returning in seconds
 * @param int $minimumTimeBetweenRuns - the minimum time between runs in milliseconds
 * @param int $spinTime - when the loop decides to not run, sleep for this time in microseconds.
 */
function continuallyExecuteCallableEx(
    $callable,
    int $maxRunTime,
    int $minimumTimeBetweenRuns,
    int $spinTime,
    $loggerCallable
) {
    $startTime = microtime(true);
    $lastRuntime = 0;
    $finished = false;

    $minimumTimeBetweenRunsInMilliseconds = $minimumTimeBetweenRuns / 1000.0;

    $loggerCallable("starting continuallyExecuteCallable");
    while ($finished === false) {
        $shouldRunThisLoop = false;
        if ($minimumTimeBetweenRuns === 0) {
            $shouldRunThisLoop = true;
        }
        else if ((microtime(true) - $lastRuntime) > $minimumTimeBetweenRunsInMilliseconds) {
            $shouldRunThisLoop = true;
        }

        if ($shouldRunThisLoop === true) {
            $callable();
            $lastRuntime = microtime(true);
        }
        else if ($spinTime != 0) {
            usleep($spinTime);
        }

        if (checkSignalsForExit()) {
            break;
        }

        if ((microtime(true) - $startTime) > $maxRunTime) {
            $loggerCallable("Reach maxRunTime - finished = true");
            $finished = true;
        }
    }

    $loggerCallable("Finishing continuallyExecuteCallable");
}

