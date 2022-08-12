<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\BaseServiceClient.php");

use DateInterval;
use DateTime;
use DateTimeZone;

class StsServiceClient extends BaseServiceClient
{
    function __construct(Configuration $configuration, string $orgCode)
    {
        parent::__construct($configuration, $configuration->Sts->IssuerUrl, $orgCode);
    }

    public function issue(): array
    {
        // build request
        $xml = \file_get_contents($_SERVER['DOCUMENT_ROOT'] . "\assets\\templates\sts-request-template.xml");
        [$requestDocument, $requestXPath] = parent::getDomXPath($xml);

        // header
        $header = $requestXPath->query("//s:Header")->item(0);

        // <a:MessageID>
        $messageIdElement = $requestXPath->query("a:MessageID", $header)->item(0);
        $messageIdElement->nodeValue = "urn:uuid:" . parent::getGuidv4();

        // <a:To>
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

        // <ds:Signature>
        $signatureElement = $requestXPath->query("ds:Signature", $securityHeader)->item(0);

        // <ds:SignedInfo>
        $signatureInfoElement = $requestXPath->query("ds:SignedInfo", $signatureElement)->item(0);

        // <ds:Reference URI="#_0"><ds:DigestValue> - timestamp
        $timestampDigestValueElement = $requestXPath->query("ds:Reference[@URI='#_0']/ds:DigestValue", $signatureInfoElement)->item(0);
        $timestampDigestValueElement->nodeValue = parent::getDigest($timestampElement->C14N(true));

        // <ds:Reference URI="#_1"><ds:DigestValue> - To
        $toDigestValueElement = $requestXPath->query("ds:Reference[@URI='#_1']/ds:DigestValue", $signatureInfoElement)->item(0);
        $toDigestValueElement->nodeValue = parent::getDigest($toElement->C14N(true));

        // <ds:SignatureValue>
        $signatureValueElement = $requestXPath->query("ds:SignatureValue", $signatureElement)->item(0);
        $content  = "-----BEGIN ENCRYPTED PRIVATE KEY-----\r\n";
        $content .= chunk_split($this->OrgData->ProtectedPrivateKey, 64);
        $content .= "-----END ENCRYPTED PRIVATE KEY-----";
        $privateKey = openssl_pkey_get_private($content, "Password1!");
        openssl_sign($signatureInfoElement->C14N(true), $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureValueElement->nodeValue = base64_encode($signature);

        // <ds:KeyInfo><o:SecurityTokenReference><o:Reference URI>
        $referenUriAttribure = $requestXPath->query("ds:KeyInfo/o:SecurityTokenReference/o:Reference/@URI", $signatureElement)->item(0);
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

        $request = $requestDocument->saveXML();
        $response = $this->ServiceClient->__doRequest($request, $this->ServiceUrl, "", \SOAP_1_2);
        return [$request, $response];
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
