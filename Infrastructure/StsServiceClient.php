<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\BaseServiceClient.php");

use DateInterval;
use DateTime;
use DOMDocument;
use DOMXPath;
use DateTimeZone;

class StsServiceClient extends BaseServiceClient
{
    function __construct(Configuration $configuration, string $orgCode)
    {
        parent::__construct($configuration, $configuration->Sts->IssuerUrl, $orgCode);
    }

    public function getSecurityTokenRequest(): string
    {
        // build request
        $templatePath = $_SERVER['DOCUMENT_ROOT'] . "\assets\\templates\sts-request-template.xml";
        $requestDocument = new DOMDocument();
        $requestDocument->load($templatePath);
        $requestXPath = new DOMXPath($requestDocument);
        $requestXPath->registerNamespace("s", "http://www.w3.org/2003/05/soap-envelope");
        $requestXPath->registerNamespace("a", "http://www.w3.org/2005/08/addressing");
        $requestXPath->registerNamespace("u", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd");
        $requestXPath->registerNamespace("o", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
        $requestXPath->registerNamespace("sig", "http://www.w3.org/2000/09/xmldsig#");
        $requestXPath->registerNamespace("trust", "http://docs.oasis-open.org/ws-sx/ws-trust/200512");
        $requestXPath->registerNamespace("wsp", "http://schemas.xmlsoap.org/ws/2004/09/policy");
        $requestXPath->registerNamespace("i", "http://schemas.xmlsoap.org/ws/2005/05/identity");
        $requestXPath->registerNamespace("wsu", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd");

        // header
        $header = $requestXPath->query("//s:Header")->item(0);

        // <a:MessageID>
        $messageIdElement = $requestXPath->query("a:MessageID", $header)->item(0);
        $messageIdElement->nodeValue = "urn:uuid:" . parent::getGuidv4();

        // <a:To s:mustUnderstand="1" u:Id="_1">
        $toElement = $requestXPath->query("a:To", $header)->item(0);
        $toElement->nodeValue = $this->ServiceUrl;

        // secutity header
        $securityHeader = $requestXPath->query("o:Security", $header)->item(0);

        // <u:Created> & <u:Expires>
        $timestampElement = $requestXPath->query("u:Timestamp", $securityHeader)->item(0);
        $created = new DateTime("now", new DateTimeZone("GMT"));
        $createdElement = $requestXPath->query("u:Created", $timestampElement)->item(0);
        $createdElement->nodeValue = parent::GetXmlUTCDateTime($created);
        $expires = $created->add(DateInterval::createFromDateString("300 seconds"));
        $expiresElement = $requestXPath->query("u:Expires", $timestampElement)->item(0);
        $expiresElement->nodeValue = parent::GetXmlUTCDateTime($expires);

        // <o:BinarySecurityToken>
        $binarySecurityTokenElement = $requestXPath->query("o:BinarySecurityToken", $securityHeader)->item(0);
        $binarySecurityTokenElement->nodeValue = $this->getBinarySecurityToken();
        $binarySecurityTokenElementIdAttribute = $requestXPath->query("o:BinarySecurityToken/@wsu:Id", $securityHeader)->item(0);
        $binarySecurityTokenElementIdAttribute->nodeValue = uniqid("uuid-");

        // <sig:Signature>
        $signatureElement = $requestXPath->query("sig:Signature", $securityHeader)->item(0);

        // <sig:SignedInfo>
        $signatureInfoElement = $requestXPath->query("sig:SignedInfo", $signatureElement)->item(0);

        // <sig:Reference URI="#_0"><sig:DigestValue> - timestamp
        $timestampDigestValueElement = $requestXPath->query("sig:Reference[@URI='#_0']/sig:DigestValue", $signatureInfoElement)->item(0);
        $timestampDigestValueElement->nodeValue = parent::getDigest($timestampElement->C14N(true));

        // <sig:Reference URI="#_1"><sig:DigestValue> - To
        $toDigestValueElement = $requestXPath->query("sig:Reference[@URI='#_1']/sig:DigestValue", $signatureInfoElement)->item(0);
        $toDigestValueElement->nodeValue = parent::getDigest($toElement->C14N(true));

        // <sig:SignatureValue>
        $signatureValueElement = $requestXPath->query("sig:SignatureValue", $signatureElement)->item(0);
        $signatureValueElement->nodeValue = parent::sign($signatureInfoElement->C14N(true));

        // <sig:KeyInfo><o:SecurityTokenReference><o:Reference URI>
        $referenUriAttribure = $requestXPath->query("sig:KeyInfo/o:SecurityTokenReference/o:Reference/@URI", $signatureElement)->item(0);
        $referenUriAttribure->nodeValue = "#" . $binarySecurityTokenElementIdAttribute->nodeValue;

        // body
        $requestSecurityTokenElement = $requestXPath->query("//s:Body/trust:RequestSecurityToken")->item(0);

        // <wsp:AppliesTo><a:EndpointReference><a:Address>
        $appliesToElement = $requestXPath->query("wsp:AppliesTo/a:EndpointReference/a:Address", $requestSecurityTokenElement)->item(0);
        $appliesToElement->nodeValue = $this->Configuration->Sts->AppliesTo;

        // <trust:Lifetime><wsu:Created> & <wsu:Expires>
        $created = new DateTime("now", new DateTimeZone("GMT"));
        $trustCreatedElement = $requestXPath->query("trust:Lifetime/wsu:Created", $requestSecurityTokenElement)->item(0);
        $trustCreatedElement->nodeValue = parent::GetXmlUTCDateTime($created);
        $expires = $created->add(DateInterval::createFromDateString("1 day"));
        $trustExpiresElement = $requestXPath->query("trust:Lifetime/wsu:Expires", $requestSecurityTokenElement)->item(0);
        $trustExpiresElement->nodeValue = parent::GetXmlUTCDateTime($expires);

        $requestXml = $requestDocument->saveXML();
        return $requestXml;
    }

    public function issue(string $requestSecurityToken): string
    {
        $response = $this->ServiceClient->__doRequest($requestSecurityToken, $this->ServiceUrl, "", \SOAP_1_2);
        return $response;
    }

    private function getBinarySecurityToken(): string
    {
        $content  = "-----BEGIN PKCS7-----\r\n";
        $content .= chunk_split($this->OrgData->PublicCertificate, 64);
        $content .= "-----END PKCS7-----";
        \openssl_pkcs7_read($content, $certificates);

        $search = array("\n", "\r", "-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----");
        $binarySecurityToken = \str_replace($search, "", $certificates[0]);
        return $binarySecurityToken;
    }
}
