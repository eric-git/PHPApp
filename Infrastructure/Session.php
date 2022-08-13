<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["ENVIRONMENT"])) {
    $_SESSION["ENVIRONMENT"] = "3PT";
}

$configuration = ConfigurationManager::getConfiguration($_SESSION["ENVIRONMENT"]);
if (!isset($_SESSION["ORGCODE"])) {
    $_SESSION["ORGCODE"] = $configuration->DefaultOrgCode;
}
