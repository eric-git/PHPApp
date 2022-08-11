<?php

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\StsController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\OperationsController.php");

$parameters = array_filter($_POST, function ($key) {
    return \str_starts_with($key, "param_");
}, ARRAY_FILTER_USE_KEY);

$controller = new (__NAMESPACE__ . "\\" . $_POST["controller"]);
$function = $_POST["function"];
$response =  \call_user_func_array([$controller, $function], array_values($parameters));
if ($controller instanceof StsController) {
    echo \json_encode($response);
} else {
    echo $response;
}
