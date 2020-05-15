
# Looping exec

A function library to allow repeatedly running a callable for a certain amount of time before returning.

Please note, parameters are open to change.

This code is currently taken directly from where it was being used in a project. There is a good argument for completely 'over-egging' the checks done in the loop for whether the code should run this time and whether a sleep should occur between runs.

But I'm not going to do that today.

## Example

```php

<?php

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

```
