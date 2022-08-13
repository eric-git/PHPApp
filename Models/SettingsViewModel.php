<?php

declare(strict_types=1);

namespace Usi\Models;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\BaseViewModel.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\ConfigurationManager.php");

use Usi\Infrastructure\ConfigurationCollection;
use Usi\Infrastructure\Configuration;

class SettingsViewModel extends BaseViewModel
{
    public ConfigurationCollection $Configuartions;
    public Configuration $CurrentConfiguration;
}
