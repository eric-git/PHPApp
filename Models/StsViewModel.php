<?php

declare(strict_types=1);

namespace Usi\Models;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\BaseViewModel.php");

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
