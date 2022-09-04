<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

use ErrorException;

function shutdownHandler(): void
{
    $error = error_get_last();
    if (isset($error) && $error["type"] == E_ERROR) {
        errorHandler($error["type"], $error["message"], $error["file"], $error["line"]);
    }
}

function errorHandler(int $errNo, string $errMsg, string $file, int $line): void
{
    $errorException = new ErrorException($errMsg, 0, $errNo, $file, $line);
    exceptionHandler($errorException);
}

function exceptionHandler($exception): void
{
    // todo: log error
    echo "<script type=\"text/javascript\">window.location=\"/error/500\";</script>";
}

register_shutdown_function("Usi\Infrastructure\\shutdownHandler");
set_error_handler("Usi\Infrastructure\\errorHandler");
set_exception_handler("Usi\Infrastructure\\exceptionHandler");
