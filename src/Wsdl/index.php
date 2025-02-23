<?php

declare(strict_types=1);

namespace Usi;

require_once(sprintf("%s/Wsdl/WsdlController.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Controllers\WsdlController;

$controller = new WsdlController();
$controller->index();
