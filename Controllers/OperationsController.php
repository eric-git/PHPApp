<?php

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
        $counter = 0;
        foreach ($operations as $operation) {
            $operationsViewModel->Operations[$counter] = new Operation($operation);
            $counter++;
        }

        return $operationsViewModel;
    }
}
