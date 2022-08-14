<?php

declare(strict_types=1);

namespace Usi;

require_once(sprintf("%s/Sts/StsController.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Controllers\StsController;

$controller = new StsController();
$controller->index();
