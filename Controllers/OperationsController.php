<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Controllers\BaseController.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\OperationsViewModel.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\UsiServiceClient.php");

use Usi\Models\OperationsViewModel;
use Usi\Infrastructure\UsiServiceClient;
use Usi\Models\Operation;
use Usi\Models\OperationCollection;

class OperationsController extends BaseController
{
    private readonly UsiServiceClient $usiServiceClient;

    function __construct()
    {
        parent::__construct();
        $this->usiServiceClient = new UsiServiceClient($this->Configuration, $this->Configuration->DefaultOrgCode);
    }

    public function populateViewModel(): OperationsViewModel
    {
        $operations = $this->usiServiceClient->getOperations();
        $operationsViewModel = new OperationsViewModel();
        $operationsViewModel->Operations = new OperationCollection();
        $requestTemplateFile = $_SERVER['DOCUMENT_ROOT'] . "\assets\\templates\Operations\%s.xml";
        $counter = 0;
        foreach ($operations as $operation) {
            $operationItem = new Operation($operation);
            $operationItem->RequestTemplate = \file_get_contents(\sprintf($requestTemplateFile, $operationItem->Name));
            $operationsViewModel->Operations[$counter] = $operationItem;
            $counter++;
        }

        return $operationsViewModel;
    }

    public function invoke(string $action, string $request):string
    {
        $response = $this->usiServiceClient->invoke("http://usi.gov.au/2022/ws/" . $action, $request);
        return parent::cleanXml($response);
    }
}
