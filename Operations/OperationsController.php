<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once(sprintf("%s/Infrastructure/BaseController.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Infrastructure/UsiServiceClient.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Operations/OperationsViewModel.php", $_SERVER["DOCUMENT_ROOT"]));

use Usi\Infrastructure\UsiServiceClient;
use Usi\Models\OperationsViewModel;
use Usi\Models\Operation;
use Usi\Models\OperationCollection;

class OperationsController extends BaseController
{
    private readonly UsiServiceClient $usiServiceClient;

    public function __construct()
    {
        parent::__construct();
        $this->usiServiceClient = new UsiServiceClient($this->Configuration, $this->OrgKeyData);
    }

    public function index(): void
    {
        $operationsViewModel = $this->populateViewModel();
        require_once(sprintf("%s/Operations/Operations.php", $_SERVER["DOCUMENT_ROOT"]));
    }

    private function populateViewModel(): OperationsViewModel
    {
        $operations = $this->usiServiceClient->getOperations();
        uasort($operations, "strcmp");
        $operationsViewModel = new OperationsViewModel();
        $operationsViewModel->Operations = new OperationCollection();
        $counter = 0;
        foreach ($operations as $operation) {
            $operationItem = new Operation($operation);
            $operationItem->RequestTemplate = file_get_contents(sprintf("%s/assets/templates/Operations/%s.xml", $_SERVER["DOCUMENT_ROOT"], $operationItem->Name));
            $operationsViewModel->Operations[$counter] = $operationItem;
            $counter++;
        }

        return $operationsViewModel;
    }

    public function invoke(string $action, string $request): array
    {
        [,, $usiRequest, $usiResponse] = $this->usiServiceClient->invoke(sprintf("http://usi.gov.au/2022/ws/%s", $action), $request);
        return [
            "UsiRequest" => parent::cleanXml($usiRequest),
            "UsiResponse" => parent::cleanXml($usiResponse)
        ];
    }
}
