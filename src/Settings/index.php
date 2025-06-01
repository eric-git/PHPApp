<?php

declare(strict_types=1);

namespace Usi;

require_once(\sprintf("%s/Settings/SettingsController.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Controllers\SettingsController;

$controller = new SettingsController();
$controller->index();
