<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\BaseController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\StsViewModel.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\StsServiceClient.php");

use Usi\Models\StsViewModel;
use Usi\Infrastructure\StsServiceClient;

class StsController extends BaseController
{
    private readonly StsServiceClient $stsServiceClient;

    public function __construct()
    {
        parent::__construct();
        $this->stsServiceClient = new StsServiceClient($this->Configuration, $this->OrgKeyData);
    }

    public function populateViewModel(): StsViewModel
    {
        $stsSettings = $this->Configuration->Sts;
        $stsViewModel = new StsViewModel($stsSettings->IssuerUrl, $stsSettings->AppliesTo);
        [$request, $response] = $this->stsServiceClient->issue();
        $stsViewModel->RequestXml = parent::cleanXml($request);
        $stsViewModel->ResponseXml = parent::cleanXml($response);
        return $stsViewModel;
    }

    public function issue(): array
    {
        [$request, $response] = $this->stsServiceClient->issue();
        return [
            "Request" => parent::cleanXml($request),
            "Response" => parent::cleanXml($response)
        ];
    }
}
