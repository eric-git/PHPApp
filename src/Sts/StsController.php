<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once(sprintf("%s/Infrastructure/BaseController.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/ServiceClients/StsServiceClient.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Sts/StsViewModel.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Models\StsViewModel;
use Usi\ServiceClients\StsServiceClient;

class StsController extends BaseController
{
  private readonly StsServiceClient $stsServiceClient;

  public function __construct()
  {
    parent::__construct();
    $this->stsServiceClient = new StsServiceClient($this->Configuration, $this->OrgKeyData);
  }

  public function index(): void
  {
    $stsViewModel = $this->populateViewModel();
    require_once(sprintf("%s/Sts/Sts.php", $_SERVER["DOCUMENT_ROOT"]));
  }

  private function populateViewModel(): StsViewModel
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
