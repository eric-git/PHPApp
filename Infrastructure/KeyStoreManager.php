<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Config.php");

use DOMDocument;
use ArrayAccess;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use Countable;
use TypeError;
use DateTime;
use DOMXPath;
use Usi\Config;

class KeyStoreManager
{
    public readonly string $Salt;
    public readonly OrgKeyDataCollection $Credentials;

    function __construct()
    {
        $configuration = Config::getConfiguration();
        $mappingData = new DOMDocument();
        $mappingData->load($_SERVER['DOCUMENT_ROOT'] . sprintf("\assets\\templates\%s\keystore-abn-code-map.xml", $configuration->Environment));
        $mappingDomXPath = new DOMXPath($mappingData);
        $mappingDomXPath->registerNamespace("x", "http://usi.gov.au/ws");

        $keyStoreData = new DOMDocument();
        $keyStoreData->load($_SERVER['DOCUMENT_ROOT'] . sprintf("\assets\\templates\%s\keystore-usi.xml", $configuration->Environment));
        $keyStoreDomXPath = new DOMXPath($keyStoreData);
        $keyStoreDomXPath->registerNamespace("x", "http://auth.abr.gov.au/credential/xsd/SBRCredentialStore");

        $this->Salt = $keyStoreDomXPath->evaluate("string(//x:credentialStore/x:salt)");
        $this->Credentials = new OrgKeyDataCollection();
        $counter = 0;
        $elements = $keyStoreDomXPath->query("//x:credential");
        foreach ($elements as $element) {
            $orgKeyData = new OrgKeyData();
            $orgKeyData->Id = $keyStoreDomXPath->evaluate("string(@id)", $element);
            $orgKeyData->IntegrityValue = $keyStoreDomXPath->evaluate("string(@integrityValue)", $element);
            $orgKeyData->CredentialSalt = $keyStoreDomXPath->evaluate("string(@credentialSalt)", $element);
            $orgKeyData->CredentialType = $keyStoreDomXPath->evaluate("string(@credentialType)", $element);
            $orgKeyData->Name1 = $keyStoreDomXPath->evaluate("string(x:name1)", $element);
            $orgKeyData->Name2 = $keyStoreDomXPath->evaluate("string(x:name2)", $element);
            $orgKeyData->ABN = $keyStoreDomXPath->evaluate("string(x:abn)", $element);
            $orgKeyData->Code = $mappingDomXPath->evaluate("string(//*[@abn='" . $orgKeyData->ABN . "']/@code)");
            $orgKeyData->LegalName = $keyStoreDomXPath->evaluate("string(x:legalName)", $element);
            $orgKeyData->PersonId = $keyStoreDomXPath->evaluate("string(x:personId)", $element);
            $orgKeyData->SerialNumber = $keyStoreDomXPath->evaluate("string(x:serialNumber)", $element);
            $orgKeyData->CreationDate = new DateTime($keyStoreDomXPath->evaluate("string(x:creationDate)", $element));
            $orgKeyData->NotBefore = new DateTime($keyStoreDomXPath->evaluate("string(x:notBefore)", $element));
            $orgKeyData->NotAfter = new DateTime($keyStoreDomXPath->evaluate("string(x:notAfter)", $element));
            $orgKeyData->Sha1fingerprint = $keyStoreDomXPath->evaluate("string(x:sha1fingerprint)", $element);
            $orgKeyData->PublicCertificate = $keyStoreDomXPath->evaluate("string(x:publicCertificate)", $element);
            $orgKeyData->ProtectedPrivateKey = $keyStoreDomXPath->evaluate("string(x:protectedPrivateKey)", $element);
            $this->Credentials[$counter] = $orgKeyData;
            $counter++;
        }
    }
}

class OrgKeyData
{
    public string $Id;
    public string $Code;
    public string $Name1;
    public string $Name2;
    public string $ABN;
    public string $LegalName;
    public string $PersonId;
    public string $SerialNumber;
    public DateTime $CreationDate;
    public DateTime $NotBefore;
    public DateTime $NotAfter;
    public string $Sha1fingerprint;
    public string $PublicCertificate;
    public string $ProtectedPrivateKey;
    public string $CredentialSalt;
    public string $IntegrityValue;
    public string $CredentialType;
}

class OrgKeyDataCollection implements ArrayAccess, IteratorAggregate, Countable
{

    private array $orgKeyData;

    function __construct(OrgKeyData ...$orgKeyData)
    {
        $this->orgKeyData = $orgKeyData;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->orgKeyData[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->orgKeyData[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof OrgKeyData) {
            $this->orgKeyData[$offset] = $value;
        } else {
            throw new TypeError("Not a OrgKeyData object.");
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->orgKeyData[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->orgKeyData);
    }

    public function count(): int
    {
        return count($this->orgKeyData);
    }
}
