<?php

use FpDbTest\Database;
use FpDbTest\DatabaseTest;
try
{
    spl_autoload_register(function ($class) {
        $a = array_slice(explode('\\', $class), 1);
        if (!$a) {
            throw new Exception();
        }
        $filename = implode('/', [__DIR__, ...$a]) . '.php';
        require_once $filename;
    });

    $mysqli = @new mysqli('localhost', 'root', '1234', 'native', 3306);
    if ($mysqli->connect_errno) {
        throw new Exception($mysqli->connect_error);
    }

    $db = new Database($mysqli);
    $test = new DatabaseTest($db);

    $test->testBuildQuery();
    $message = "\033[32mOK\033[0m. Tests done.\n";
} catch (\Exception $e)
{
    $message = "\033[31mError\033[0m. Tests failed. {$e->getMessage()}\n";
}

exit($message);
