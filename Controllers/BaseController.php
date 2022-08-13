<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\ConfigurationManager.php");

use DOMDocument;
use XSLTProcessor;
use Usi\Infrastructure\Configuration;
use Usi\Infrastructure\ConfigurationManager;
use Usi\Infrastructure\OrgKeyData;

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
        $domDocument->load(\sprintf("%s\assets\\templates\xml-cleanup.xslt", $_SERVER['DOCUMENT_ROOT']));
        $xslProcessor = new XSLTProcessor();
        $xslProcessor->importStyleSheet($domDocument);
        $xmlDocument = new DOMDocument();
        $xmlDocument->loadXML($xml);
        return $xslProcessor->transformToXML($xmlDocument);
    }
}
