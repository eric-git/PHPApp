<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\StsController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\WsdlController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\OperationsController.php");

header("Content-Type: application/json", true);
$parameters = array_filter($_POST, function ($key) {
    return \str_starts_with($key, "param_");
}, ARRAY_FILTER_USE_KEY);
$controller = new (\sprintf("%s\\%s", __NAMESPACE__, $_POST["controller"]));
$function = $_POST["function"];
$response =  \call_user_func_array([$controller, $function], array_values($parameters));
echo \json_encode($response);
