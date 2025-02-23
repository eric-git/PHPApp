<?php

declare(strict_types=1);

namespace Usi;

require_once(sprintf("%s/Operations/OperationsController.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Controllers\OperationsController;

$controller = new OperationsController();
$controller->index();
