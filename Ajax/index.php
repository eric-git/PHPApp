<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once(sprintf("%s/Sts/StsController.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Wsdl/WsdlController.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Operations/OperationsController.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Settings/SettingsController.php", $_SERVER["DOCUMENT_ROOT"]));

header("Content-Type: application/json", true);
$parameters = array_filter($_POST, function ($key) {
    return str_starts_with($key, "param_");
}, ARRAY_FILTER_USE_KEY);
$controller = new (sprintf("%s\\%s", __NAMESPACE__, $_POST["controller"]));
$function = $_POST["function"];
$response =  call_user_func_array([$controller, $function], array_values($parameters));
echo json_encode($response);
