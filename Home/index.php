<?php

declare(strict_types=1);

namespace Usi;

require_once(sprintf("%s/Home/HomeController.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Controllers\HomeController;

$controller = new HomeController();
$controller->index();
