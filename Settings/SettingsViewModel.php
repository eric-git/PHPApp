<?php

declare(strict_types=1);

namespace Usi\Models;

require_once(sprintf("%s/Infrastructure/BaseViewModel.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Infrastructure/ConfigurationManager.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Infrastructure\ConfigurationCollection;

class SettingsViewModel extends BaseViewModel
{
    public readonly string $CurrentEnvironment;
    public readonly string $CurrentOrgCode;
    public readonly ConfigurationCollection $ConfiguartionCollection;

    public function __construct(string $currentEnvironment, string $currentOrgCode, ConfigurationCollection $configuartionCollection)
    {
        $this->CurrentEnvironment = $currentEnvironment;
        $this->CurrentOrgCode = $currentOrgCode;
        $this->ConfiguartionCollection = $configuartionCollection;
    }
}
