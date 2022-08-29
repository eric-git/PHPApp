<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once(sprintf("%s/Infrastructure/Session.php", $_SERVER["DOCUMENT_ROOT"]));

use DOMDocument;
use XSLTProcessor;
use Usi\Configuration\Configuration;
use Usi\Configuration\ConfigurationManager;
use Usi\Configuration\OrgKeyData;

abstract class BaseController
{
    protected readonly Configuration $Configuration;
    protected readonly OrgKeyData $OrgKeyData;

    protected function __construct()
    {
        $this->Configuration = ConfigurationManager::getConfiguration($_SESSION["ENVIRONMENT"]);
        $this->OrgKeyData = $this->Configuration->getOrgKeyData($_SESSION["ORGCODE"]);
    }

    protected static function cleanXml($xml): string
    {
        $domDocument = new DOMDocument();
        $domDocument->load(sprintf("%s/assets/templates/xml-cleanup.xslt", $_SERVER["DOCUMENT_ROOT"]));
        $xslProcessor = new XSLTProcessor();
        $xslProcessor->importStyleSheet($domDocument);
        $xmlDocument = new DOMDocument();
        $xmlDocument->loadXML($xml);
        return $xslProcessor->transformToXML($xmlDocument);
    }
}
