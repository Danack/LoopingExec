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
 *
 *
 * @param callable $callable - the thing to run
 * @param int $secondsBetweenRuns - the minimum time between runs in milliseconds
 * @param int $sleepTime - the time to sleep between runs in milliseconds
 * @param int $maxRunTime - the max time to run for, before returning in seconds
 */

/**
 * Repeatedly calls a callable until it's time to stop
 *
 *
 * @param $callable - the callable  to run
 * @param int $maxRunTimeInSeconds the max time to run for, before returning in seconds
 * @param int $minimumTimeBetweenRunsInMilliseconds - the minimum time between runs in milliseconds
 */
function continuallyExecuteCallable(
    $callable,
    int $maxRunTimeInSeconds,
    int $minimumTimeBetweenRunsInMilliseconds
) {

    $loopManager = new TimeExecControl(
        $maxRunTimeInSeconds,
        $minimumTimeBetweenRunsInMilliseconds,
        50,
        60
    );
    continuallyExecuteCallableEx(
        $callable,
        $loopManager,
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
    ExecControl $execControl,
    $loggerCallable
) {

    $finished = false;

    $execControl->start();
    $loggerCallable("starting continuallyExecuteCallable");
    while ($finished === false) {
        $shouldRunThisLoop = $execControl->shouldRun();
        if ($shouldRunThisLoop === true) {
            $callable();
            $execControl->wasRun();
        }

        if (checkSignalsForExit()) {
            $loggerCallable("Exiting after signal");
            break;
        }

        if ($execControl->shouldEnd() === true) {
            $finished = true;
            $loggerCallable("execControl said that looping should end.");
        }
    }

    $loggerCallable("Finishing continuallyExecuteCallable");
}
