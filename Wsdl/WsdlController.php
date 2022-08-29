<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once(sprintf("%s/Infrastructure/BaseController.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/ServiceClients/UsiServiceClient.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Wsdl/WsdlViewModel.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Models\WsdlViewModel;
use Usi\ServiceClients\UsiServiceClient;

class WsdlController extends BaseController
{
    private readonly UsiServiceClient $usiServiceClient;

    public function __construct()
    {
        parent::__construct();
        $this->usiServiceClient = new UsiServiceClient($this->Configuration, $this->OrgKeyData);
    }

    public function index(): void
    {
        $wsdlViewModel = $this->populateViewModel();
        require_once(sprintf("%s/Wsdl/Wsdl.php", $_SERVER["DOCUMENT_ROOT"]));
    }

    private function populateViewModel(): WsdlViewModel
    {
        $originalWsdl = $this->usiServiceClient->getWsdl();
        $wsdlViewModel = new WsdlViewModel($originalWsdl);
        $wsdlViewModel->Wsdl = parent::cleanXml($originalWsdl);
        return $wsdlViewModel;
    }

    public function getWsdl(): array
    {
        $originalWsdl = $this->usiServiceClient->getWsdl();
        return ["Wsdl" => parent::cleanXml($originalWsdl)];
    }
}
