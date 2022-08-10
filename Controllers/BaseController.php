<?php

namespace Usi\Controllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Config.php");

use DOMDocument;
use XSLTProcessor;
use Usi\Config;
use Usi\Infrastructure\Configuration;

class BaseController
{
    protected readonly Configuration $Configuration;

    function __construct()
    {
        $this->Configuration = Config::getConfiguration();
    }

    protected static function cleanXml($xml): string
    {
        $domDocument = new DOMDocument();
        $domDocument->load($_SERVER['DOCUMENT_ROOT'] . "\assets\\templates\xml-cleanup.xslt");
        $xslProcessor = new XSLTProcessor();
        $xslProcessor->importStyleSheet($domDocument);
        $xmlDocument = new DOMDocument();
        $xmlDocument->loadXML($xml);
        return $xslProcessor->transformToXML($xmlDocument);
    }
}
