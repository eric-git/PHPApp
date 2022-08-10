<?php

namespace Usi\Infrastructure;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\KeyStoreManager.php");

use DateTime;
use DateTimeZone;
use SoapClient;

class BaseServiceClient
{
    protected readonly SoapClient $ServiceClient;
    protected readonly Configuration $Configuration;
    protected readonly string $ServiceUrl;
    protected readonly OrgKeyData $OrgData;

    function __construct(Configuration $configuration, string $serviceUrl, string $orgCode)
    {
        $this->Configuration = $configuration;
        $this->ServiceUrl = $serviceUrl;
        $wsdlUrl = $this->getWsdlUrl($serviceUrl);
        $options = array();
        if (isset($configuration->Proxy)) {
            // todo: make proxy work
        }
        $this->ServiceClient = new SoapClient($wsdlUrl, $options);
        $keyStore = new KeyStoreManager();
        foreach ($keyStore->Credentials as $credential) {
            if (strcasecmp($credential->Code, $orgCode) === 0) {
                $this->OrgData = $credential;
                break;
            }
        }
    }

    protected function sign(string $cannonicalizedXml, string $password = "Password1!"): string
    {
        $content  = "-----BEGIN ENCRYPTED PRIVATE KEY-----\r\n";
        $content .= chunk_split($this->OrgData->ProtectedPrivateKey, 64);
        $content .= "-----END ENCRYPTED PRIVATE KEY-----";
        $privateKey = openssl_pkey_get_private($content, $password);
        openssl_sign($cannonicalizedXml, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    protected function getWsdlUrl(): string
    {
        $wsdlUrl = $this->ServiceUrl . "?wsdl";
        return $wsdlUrl;
    }

    protected static function getDigest(string $cannonicalizedXml, string $algorithm = "sha256"): string
    {
        $hash = hash($algorithm, $cannonicalizedXml, true);
        return base64_encode($hash);
    }

    protected static function getGuidv4(): string
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        }

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    protected static function GetXmlUTCDateTime(DateTime $dateTime): string
    {
        $timeZoneName = $dateTime->getTimezone()->getName();
        $utcTimeZone = new DateTimeZone("GMT");
        if (strcasecmp($timeZoneName, $utcTimeZone->getName()) !== 0) {
            $dateTime->setTimezone($utcTimeZone);
        }

        $formatted = $dateTime->format("c");
        return str_replace('+00:00', '.000Z', $formatted);
    }
}
