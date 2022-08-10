<?php

namespace Usi\Infrastructure;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\BaseServiceClient.php");

class UsiServiceClient extends BaseServiceClient
{
    function __construct(Configuration $configuration, string $orgCode)
    {
        parent::__construct($configuration, $configuration->UsiServiceUrl, $orgCode);
    }

    public function getWsdl(): string
    {
        $wsdlUrl = $this->getWsdlUrl();
        $wsdl = file_get_contents($wsdlUrl);
        return $wsdl;
    }

    public function getOperations(): array
    {
        $functions = $this->ServiceClient->__getFunctions();
        return $functions;
    }
}
