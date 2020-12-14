<?php

declare(strict_types = 1);

namespace LoopingExec;

interface ExecControl
{
    public function start(): void;

    public function shouldEnd(): bool;

    public function shouldRun(): bool;

    public function wasRun(): void;
}
