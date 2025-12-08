<?php

declare(strict_types=1);

namespace Usi\Models;

require_once(sprintf("%s/Infrastructure/BaseViewModel.php", $_SERVER["DOCUMENT_ROOT"]));

class StsViewModel extends BaseViewModel
{
  public readonly string $IssuerUrl;
  public readonly string $AppliesTo;

  public string $OrgCode;
  public string $RequestXml = "";
  public string $ResponseXml = "";

  public function __construct(string $issuerUrl, string $appliesTo)
  {
    $this->IssuerUrl = $issuerUrl;
    $this->AppliesTo = $appliesTo;
  }
}
