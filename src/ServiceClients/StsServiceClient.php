<?php

declare(strict_types=1);

namespace Usi\ServiceClients;

require_once(sprintf("%s/Infrastructure/BaseServiceClient.php", $_SERVER["DOCUMENT_ROOT"]));

use DateInterval;
use DateTime;
use DateTimeZone;
use Usi\Configuration\Configuration;
use Usi\Configuration\OrgKeyData;

class StsServiceClient extends BaseServiceClient
{
    public function __construct(Configuration $configuration, OrgKeyData $orgKeyData)
    {
        parent::__construct($configuration, $configuration->Sts->IssuerUrl, $orgKeyData);
    }

    public function issue(): array
    {
        // build request
        $templateFile = empty($this->OrgData->SecondPartyAbn) ? "sts-request-template.xml" : "sts-on-behalf-of-request-template.xml";
        $xml = file_get_contents(sprintf("%s/assets/templates/%s", $_SERVER["DOCUMENT_ROOT"], $templateFile));
        [$requestDocument, $requestXPath] = parent::getDomXPath($xml);

        // header
        $header = $requestXPath->query("//s:Header")->item(0);

        // <a:MessageID>
        $messageIdElement = $requestXPath->query("a:MessageID", $header)->item(0);
        $messageIdElement->nodeValue = sprintf("urn:uuid:%s", parent::getGuidV4());

        // <a:To>
        $toElement = $requestXPath->query("a:To", $header)->item(0);
        $toElement->nodeValue = $this->ServiceUrl;

        // security header
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
        $content = sprintf("-----BEGIN PKCS7-----%s%s-----END PKCS7-----", PHP_EOL, chunk_split($this->OrgData->PublicCertificate, 64));
        openssl_pkcs7_read($content, $certificates);
        $binarySecurityToken = str_replace([PHP_EOL, "-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----"], "", $certificates[0]);
        $binarySecurityTokenElement->nodeValue = $binarySecurityToken;
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
        $content = sprintf("-----BEGIN ENCRYPTED PRIVATE KEY-----%s%s-----END ENCRYPTED PRIVATE KEY-----", PHP_EOL, chunk_split($this->OrgData->ProtectedPrivateKey, 64));
        $privateKey = openssl_pkey_get_private($content, $this->OrgData->PrivateKeyPassword);
        openssl_sign($signatureInfoElement->C14N(true), $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureValueElement->nodeValue = base64_encode($signature);

        // <ds:KeyInfo><o:SecurityTokenReference><o:Reference URI>
        $referenceUriAttribute = $requestXPath->query("ds:KeyInfo/o:SecurityTokenReference/o:Reference/@URI", $signatureElement)->item(0);
        $referenceUriAttribute->nodeValue = sprintf("#%s", $binarySecurityTokenElementIdAttribute->nodeValue);

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

        if (!empty($this->OrgData->SecondPartyAbn)) {
            $relationshipTokenElement = $requestXPath->query("trust2008:ActAs/v13:RelationshipToken", $requestSecurityTokenElement)->item(0);

            // @ID
            $idAttribute = $requestXPath->query("@v13:ID", $relationshipTokenElement)->item(0);
            $idAttribute->nodeValue = parent::getGuidV4();

            // <v13:Relationship>/<v13:Attribute> @v13:Value
            $ssidValueAttribute = $requestXPath->query("v13:Relationship/v13:Attribute[@v13:Name='SSID']/@v13:Value", $relationshipTokenElement)->item(0);
            $ssidValueAttribute->nodeValue = "0000123400";

            // <v13:FirstParty> @Value
            $firstPartyValueAttribute = $requestXPath->query("v13:FirstParty/@v13:Value", $relationshipTokenElement)->item(0);
            $firstPartyValueAttribute->nodeValue = $this->OrgData->ABN;
            
            // <v13:SecondParty> @Value
            $secondPartyValueAttribute = $requestXPath->query("v13:SecondParty/@v13:Value", $relationshipTokenElement)->item(0);
            $secondPartyValueAttribute->nodeValue = $this->OrgData->SecondPartyAbn;
        }

        $request = $requestDocument->saveXML();
        $response = $this->ServiceClient->__doRequest($request, $this->ServiceUrl, "", SOAP_1_2);
        return [$request, $response];
    }
}
