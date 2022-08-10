<?php

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\BaseController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\StsViewModel.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\StsServiceClient.php");

use Usi\Models\StsViewModel;
use Usi\Infrastructure\StsServiceClient;

class StsController extends BaseController
{
    private readonly StsServiceClient $stsServiceClient;

    function __construct()
    {
        parent::__construct();
        $this->stsServiceClient = new StsServiceClient($this->Configuration, $this->Configuration->DefaultOrgCode);
    }

    public function populateViewModel(): StsViewModel
    {
        $stsSettings = $this->Configuration->Sts;
        $stsViewModel = new StsViewModel($stsSettings->IssuerUrl, $stsSettings->AppliesTo);
        $request = $this->stsServiceClient->getSecurityTokenRequest();
        $stsViewModel->RequestXml = parent::cleanXml($request);
        //$response = $this->issue($request);
        //$stsViewModel->ResponseXml = parent::cleanXml($response);
        return $stsViewModel;
    }

    public function issue(string $request): string
    {
        $response = $this->stsServiceClient->issue($request);
        return $response;
    }
}
