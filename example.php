<?php

declare(strict_types = 1);

require __DIR__ . "/vendor/autoload.php";

use function LoopingExec\continuallyExecuteCallable;

$fn = function () {
    static $count = 0;
    echo "Hello world: $count\n";
    $count += 1;
};

continuallyExecuteCallable(
    $fn,
    5,
    1000,
    0
);


// output is:
// Hello world: 0
// Hello world: 1
// Hello world: 2
// Hello world: 3
// Hello world: 4
