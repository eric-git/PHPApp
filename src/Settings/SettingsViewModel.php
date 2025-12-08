<?php

declare(strict_types=1);

namespace Usi\Models;

require_once(sprintf("%s/Infrastructure/BaseViewModel.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Infrastructure/ConfigurationManager.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Configuration\ConfigurationCollection;

class SettingsViewModel extends BaseViewModel
{
  public readonly string $CurrentEnvironment;
  public readonly string $CurrentOrgCode;
  public readonly ConfigurationCollection $ConfigurationCollection;

  public function __construct(string $currentEnvironment, string $currentOrgCode, ConfigurationCollection $configurationCollection)
  {
    $this->CurrentEnvironment = $currentEnvironment;
    $this->CurrentOrgCode = $currentOrgCode;
    $this->ConfigurationCollection = $configurationCollection;
  }
}
