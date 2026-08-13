<?php

declare(strict_types=1);

namespace Usi\Controllers;

require_once(sprintf("%s/Infrastructure/Error.php", $_SERVER["DOCUMENT_ROOT"]));
require_once(sprintf("%s/Infrastructure/Session.php", $_SERVER["DOCUMENT_ROOT"]));

use DOMDocument;
use XSLTProcessor;
use Usi\Configuration\Configuration;
use Usi\Configuration\ConfigurationManager;
use Usi\Configuration\OrgKeyData;
use Usi\Configuration\UsiVersion;

abstract class BaseController
{
  protected readonly Configuration $Configuration;
  protected readonly OrgKeyData $OrgKeyData;
  protected readonly UsiVersion $VersionData;

  protected function __construct()
  {
    $this->Configuration = ConfigurationManager::getConfiguration($_SESSION["ENVIRONMENT"]);
    $this->OrgKeyData = $this->Configuration->getOrgKeyData($_SESSION["ORGCODE"]);
    $this->VersionData = $this->Configuration->getUsiVersion($_SESSION["VERSION"]);
  }

  protected static function cleanXml(string $xml): string
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
