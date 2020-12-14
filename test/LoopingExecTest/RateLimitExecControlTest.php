<?php

declare(strict_types=1);

namespace LoopingExecTest;

use LoopingExec\RateLimitExecControl;
use function LoopingExec\nullLogger;
use function LoopingExec\continuallyExecuteCallableEx;

/*
 * @coversNothing
 */
class RateLimitExecControlTest extends BaseTestCase
{
    /**
     * @cover RateLimitExecControl
     */
    public function testBasic()
    {
        $execControl = new RateLimitExecControl(
            $numberPerTimePeriod = 2,
            $timePeriod = 10,
            $maxRunTime = 30,
            20
        );

        $execCounter = 0;

        $fn = function () use (&$execCounter) {
            $execCounter += 1;
        };

        continuallyExecuteCallableEx(
            $fn,
            $execControl,
            'LoopingExec\nullLogger'
        );

        $expectedNumberOfRuns = 6;

        $message = "Number of execs was meant to be $expectedNumberOfRuns but was $execCounter";

        $this->assertSame(
            $expectedNumberOfRuns,
            $execCounter,
            $message
        );
    }
}
