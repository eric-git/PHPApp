<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\BaseController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\WsdlViewModel.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\UsiServiceClient.php");

use Usi\Models\WsdlViewModel;
use Usi\Infrastructure\UsiServiceClient;

class WsdlController extends BaseController
{
    private readonly UsiServiceClient $usiServiceClient;

    public function __construct()
    {
        parent::__construct();
        $this->usiServiceClient = new UsiServiceClient($this->Configuration, $this->OrgKeyData);
    }

    public function populateViewModel(): WsdlViewModel
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
