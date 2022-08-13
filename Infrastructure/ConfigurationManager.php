<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Infrastructure\KeyStore.php");

use ArrayAccess;
use IteratorAggregate;
use Countable;
use Traversable;
use TypeError;
use ArrayIterator;
use DOMDocument;
use DOMXPath;
use DateTime;

class ConfigurationManager
{
    public static ConfigurationCollection $Configurations;

    public static function getConfiguration(string $environment): Configuration
    {
        self::initialize();
        foreach (self::$Configurations as $configuration) {
            if (strcasecmp($configuration->Environment, $environment) === 0) {
                return $configuration;
            }
        }
    }

    private static function initialize(): void
    {
        if (isset(self::$Configurations)) {
            return;
        }

        self::$Configurations = new ConfigurationCollection(
            // LOCAL
            new Configuration(
                "LOCAL",
                new StsSettings(
                    "https://softwareauthorisations.acc.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://3pt.portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://localhost:4443/service/v5/usiservice.svc",
                "VA1803",
                self::getKeyStore("LOCAL"),
                new ProxySettings("dmz", 8080)
            ),

            // DEV
            new Configuration(
                "DEV",
                new StsSettings(
                    "https://softwareauthorisations.acc.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://3pt.portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://dev.portal.usi.gov.au/service/v5/usiservice.svc",
                "VA1803",
                self::getKeyStore("DEV")
            ),

            // 3PT
            new Configuration(
                "3PT",
                new StsSettings(
                    "https://softwareauthorisations.acc.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://3pt.portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://3pt.portal.usi.gov.au/service/v5/usiservice.svc",
                "VA1803",
                self::getKeyStore("3PT")
            ),

            // PROD
            new Configuration(
                "PROD",
                new StsSettings(
                    "https://softwareauthorisations.ato.gov.au/R3.0/S007v1.3/service.svc",
                    "https://portal.usi.gov.au/service/usiservice.svc"
                ),
                "https://portal.usi.gov.au/service/v5/usiservice.svc",
                "VA1803",
                self::getKeyStore("PROD")
            )
        );
    }

    private static function getKeyStore(string $environment): KeyStore
    {
        $keyStore = new KeyStore();

        $mappingData = new DOMDocument();
        $mappingData->load(sprintf("%s\assets\\templates\%s\keystore-usi-map.xml", $_SERVER['DOCUMENT_ROOT'], $environment));
        $mappingDomXPath = new DOMXPath($mappingData);
        $mappingDomXPath->registerNamespace("x", "http://usi.gov.au/ws");

        $keyStoreData = new DOMDocument();
        $keyStoreData->load(sprintf("%s\assets\\templates\%s\keystore-usi.xml", $_SERVER['DOCUMENT_ROOT'], $environment));
        $keyStoreDomXPath = new DOMXPath($keyStoreData);
        $keyStoreDomXPath->registerNamespace("x", "http://auth.abr.gov.au/credential/xsd/SBRCredentialStore");

        $keyStore->Salt = $keyStoreDomXPath->evaluate("string(//x:credentialStore/x:salt)");
        $keyStore->Credentials = new OrgKeyDataCollection();
        $counter = 0;
        $elements = $keyStoreDomXPath->query("//x:credential");
        foreach ($elements as $element) {
            $orgKeyData = new OrgKeyData();
            $orgKeyData->ABN = $keyStoreDomXPath->evaluate("string(x:abn)", $element);
            $mappingElement = $mappingDomXPath->query(\sprintf("//*[@abn='%s']", $orgKeyData->ABN))->item(0);
            $orgKeyData->Id = $keyStoreDomXPath->evaluate("string(@id)", $element);
            $orgKeyData->IntegrityValue = $keyStoreDomXPath->evaluate("string(@integrityValue)", $element);
            $orgKeyData->CredentialSalt = $keyStoreDomXPath->evaluate("string(@credentialSalt)", $element);
            $orgKeyData->CredentialType = $keyStoreDomXPath->evaluate("string(@credentialType)", $element);
            $orgKeyData->Name1 = $keyStoreDomXPath->evaluate("string(x:name1)", $element);
            $orgKeyData->Name2 = $keyStoreDomXPath->evaluate("string(x:name2)", $element);
            $orgKeyData->Code = $mappingDomXPath->evaluate("string(@code)", $mappingElement);
            $orgKeyData->LegalName = $keyStoreDomXPath->evaluate("string(x:legalName)", $element);
            $orgKeyData->PersonId = $keyStoreDomXPath->evaluate("string(x:personId)", $element);
            $orgKeyData->SerialNumber = $keyStoreDomXPath->evaluate("string(x:serialNumber)", $element);
            $orgKeyData->CreationDate = new DateTime($keyStoreDomXPath->evaluate("string(x:creationDate)", $element));
            $orgKeyData->NotBefore = new DateTime($keyStoreDomXPath->evaluate("string(x:notBefore)", $element));
            $orgKeyData->NotAfter = new DateTime($keyStoreDomXPath->evaluate("string(x:notAfter)", $element));
            $orgKeyData->Sha1fingerprint = $keyStoreDomXPath->evaluate("string(x:sha1fingerprint)", $element);
            $orgKeyData->PublicCertificate = $keyStoreDomXPath->evaluate("string(x:publicCertificate)", $element);
            $orgKeyData->ProtectedPrivateKey = $keyStoreDomXPath->evaluate("string(x:protectedPrivateKey)", $element);
            $orgKeyData->PrivateKeyPassword = $mappingDomXPath->evaluate("string(@privateKeyPassword)", $mappingElement);
            $keyStore->Credentials[$counter] = $orgKeyData;
            $counter++;
        }

        return $keyStore;
    }
}

class StsSettings
{
    public readonly string $IssuerUrl;
    public readonly string $AppliesTo;

    public function __construct(string $issuerUrl, string $appliesTo)
    {
        $this->IssuerUrl = $issuerUrl;
        $this->AppliesTo = $appliesTo;
    }
}

class ProxySettings
{
    public readonly string $Url;
    public readonly int $Port;
    public readonly ?string $Username;
    public readonly ?string $Password;

    public function __construct(string $url, int $port, string $username = null, string $password = null)
    {
        $this->Url = $url;
        $this->Port = $port;
        $this->Username = $username;
        $this->Password = $password;
    }
}

class Configuration
{
    public readonly string $Environment;
    public readonly StsSettings $Sts;
    public readonly string $UsiServiceUrl;
    public readonly string $DefaultOrgCode;
    public readonly ?ProxySettings $Proxy;
    public readonly KeyStore $KeyStore;

    public function __construct(string $environment, StsSettings $sts, string $usiServiceUrl, string $defaultOrgCode, KeyStore $keyStore, ProxySettings $proxy = null)
    {
        $this->Environment = $environment;
        $this->Sts = $sts;
        $this->UsiServiceUrl = $usiServiceUrl;
        $this->DefaultOrgCode = $defaultOrgCode;
        $this->Proxy = $proxy;
        $this->KeyStore = $keyStore;
    }

    public function getOrgKeyData(string $orgCode): OrgKeyData
    {
        foreach ($this->KeyStore->Credentials as $credential) {
            if (strcasecmp($credential->Code, $orgCode) === 0) {
                return $credential;
                break;
            }
        }
    }
}

class ConfigurationCollection implements ArrayAccess, IteratorAggregate, Countable
{

    private array $configurations;

    public function __construct(Configuration ...$configurations)
    {
        $this->configurations = $configurations;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->configurations[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->configurations[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof Configuration) {
            $this->configurations[$offset] = $value;
        } else {
            throw new TypeError("Not a Configuration object.");
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->configurations[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->configurations);
    }

    public function count(): int
    {
        return count($this->configurations);
    }
}
