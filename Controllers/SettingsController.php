<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\BaseController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\ConfigurationManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\SettingsViewModel.php");

use Usi\Models\SettingsViewModel;

class SettingsController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function populateViewModel(): SettingsViewModel
    {
        return new SettingsViewModel();
    }
}
