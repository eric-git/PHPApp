<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once(sprintf("%s/Infrastructure/BaseController.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Infrastructure/ConfigurationManager.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Settings/SettingsViewModel.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Infrastructure\ConfigurationManager;
use Usi\Models\SettingsViewModel;

class SettingsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $settingsViewModel = $this->populateViewModel();
        require_once(sprintf("%s/Settings/Settings.php", $_SERVER["DOCUMENT_ROOT"]));
    }

    public function update(string $environment, string $orgCode): array
    {
        $_SESSION["ENVIRONMENT"] = $environment;
        $_SESSION["ORGCODE"] = $orgCode;
        return ["Environment" => $environment, "OrgCode" => $orgCode];
    }

    private function populateViewModel(): SettingsViewModel
    {
        $settingsViewModel = new SettingsViewModel($this->Configuration->Environment, $this->OrgKeyData->Code, ConfigurationManager::$Configurations);
        return $settingsViewModel;
    }
}
