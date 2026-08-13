<?php

declare(strict_types=1);

namespace Usi\Configuration;

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
    throw new \Exception("Configuration for environment '{$environment}' not found.");
  }

  private static function initialize(): void
  {
    if (isset(self::$Configurations)) {
      return;
    }

    $baseDirectory = sprintf("%s/assets/configuration", $_SERVER["DOCUMENT_ROOT"]);
    self::$Configurations = new ConfigurationCollection();
    $counter = 0;
    $environmentPaths = glob(sprintf("%s/*", $baseDirectory), GLOB_ONLYDIR);
    foreach ($environmentPaths as $environmentPath) {
      preg_match("/[^\/]+$/", $environmentPath, $environment);
      [$environmentDomXPath, $keyStoreDomXPath] = self::getEnvironmentDomXPath($environmentPath);
      self::$Configurations[$counter] = self::getEnvironment($environment[0], $environmentDomXPath, $keyStoreDomXPath);
      $counter++;
    }
  }

  private static function getEnvironment(string $environment, DomXPath $environmentDomXPath, DomXPath $keyStoreDomXPath): Configuration
  {
    $environmentElement = $environmentDomXPath->query("//usi:environment")->item(0);
    $configuration = new Configuration(
      $environment,
      $environmentDomXPath->evaluate("string(@url)", $environmentElement),
      $environmentDomXPath->evaluate("string(@defaultOrgCode)", $environmentElement),
      $environmentDomXPath->evaluate("string(@defaultVersion)", $environmentElement),
      self::getStsSettings($environmentDomXPath),
      self::getKeyStore($keyStoreDomXPath, $environmentDomXPath),
      self::getUsiSettings($environmentDomXPath),
    );

    return $configuration;
  }

  private static function getStsSettings(DomXPath $environmentDomXPath): StsSettings
  {
    $stsElement = $environmentDomXPath->query("//usi:environment/usi:sts")->item(0);
    $stsSettings = new StsSettings(
      $environmentDomXPath->evaluate("string(@url)", $stsElement),
      $environmentDomXPath->evaluate("string(@uri)", $stsElement)
    );

    return $stsSettings;
  }

  private static function getKeyStore(DomXPath $keyStoreDomXPath, DomXPath $environmentDomXPath): KeyStore
  {
    $keyStore = new KeyStore();
    $keyStore->Salt = $keyStoreDomXPath->evaluate("string(//ato:credentialStore/ato:salt)");
    $keyStore->Credentials = new OrgKeyDataCollection();
    $counter = 0;
    $elements = $keyStoreDomXPath->query("//ato:credential");
    foreach ($elements as $element) {
      $orgKeyData = new OrgKeyData();
      $orgKeyData->ABN = $keyStoreDomXPath->evaluate("string(ato:abn)", $element);
      $mappingElement = $environmentDomXPath->query(sprintf("//usi:environment/usi:sts/usi:keyStoreMapping/usi:add[@abn='%s']", $orgKeyData->ABN))->item(0);
      $orgKeyData->Id = $keyStoreDomXPath->evaluate("string(@id)", $element);
      $orgKeyData->IntegrityValue = $keyStoreDomXPath->evaluate("string(@integrityValue)", $element);
      $orgKeyData->CredentialSalt = $keyStoreDomXPath->evaluate("string(@credentialSalt)", $element);
      $orgKeyData->CredentialType = $keyStoreDomXPath->evaluate("string(@credentialType)", $element);
      $orgKeyData->Name1 = $keyStoreDomXPath->evaluate("string(ato:name1)", $element);
      $orgKeyData->Name2 = $keyStoreDomXPath->evaluate("string(ato:name2)", $element);
      $orgKeyData->Code = $environmentDomXPath->evaluate("string(@code)", $mappingElement);
      $orgKeyData->LegalName = $keyStoreDomXPath->evaluate("string(ato:legalName)", $element);
      $orgKeyData->PersonId = $keyStoreDomXPath->evaluate("string(ato:personId)", $element);
      $orgKeyData->SerialNumber = $keyStoreDomXPath->evaluate("string(ato:serialNumber)", $element);
      $orgKeyData->CreationDate = new DateTime($keyStoreDomXPath->evaluate("string(ato:creationDate)", $element));
      $orgKeyData->NotBefore = new DateTime($keyStoreDomXPath->evaluate("string(ato:notBefore)", $element));
      $orgKeyData->NotAfter = new DateTime($keyStoreDomXPath->evaluate("string(ato:notAfter)", $element));
      $orgKeyData->Sha1fingerprint = $keyStoreDomXPath->evaluate("string(ato:sha1fingerprint)", $element);
      $orgKeyData->PublicCertificate = $keyStoreDomXPath->evaluate("string(ato:publicCertificate)", $element);
      $orgKeyData->ProtectedPrivateKey = $keyStoreDomXPath->evaluate("string(ato:protectedPrivateKey)", $element);
      $orgKeyData->PrivateKeyPassword = $environmentDomXPath->evaluate("string(@privateKeyPassword)", $mappingElement);
      $orgKeyData->SecondPartyAbn = $environmentDomXPath->evaluate("string(@secondPartyAbn)", $mappingElement);
      $keyStore->Credentials[$counter] = $orgKeyData;
      $counter++;
    }

    return $keyStore;
  }

  private static function getEnvironmentDomXPath(string $containerPath): array
  {
    $environmentData = new DOMDocument();
    $environmentData->load(sprintf("%s/environment.xml", $containerPath));
    $environmentDomXPath = new DOMXPath($environmentData);
    $environmentDomXPath->registerNamespace("usi", "http://usi.gov.au/ws");

    $keyStoreData = new DOMDocument();
    $keyStoreData->load(sprintf("%s/keystore-usi.xml", $containerPath));
    $keyStoreDomXPath = new DOMXPath($keyStoreData);
    $keyStoreDomXPath->registerNamespace("ato", "http://auth.abr.gov.au/credential/xsd/SBRCredentialStore");

    return [$environmentDomXPath, $keyStoreDomXPath];
  }

  private static function getUsiSettings(DomXPath $environmentDomXPath): UsiSettings
  {
    $usiVersions = new UsiVersionCollection();
    $counter = 0;
    $elements = $environmentDomXPath->query("//usi:environment/usi:usi/usi:version");
    foreach ($elements as $element) {
      $usiVersion = new UsiVersion(
        $environmentDomXPath->evaluate("string(@name)", $element),
        $environmentDomXPath->evaluate("string(@url)", $element)
      );
      $usiVersions[$counter] = $usiVersion;
      $counter++;
    }

    return new UsiSettings($usiVersions);
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

class KeyStore
{
  public string $Salt;
  public OrgKeyDataCollection $Credentials;
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
  public string $PrivateKeyPassword;
  public string $SecondPartyAbn;
}

class OrgKeyDataCollection implements ArrayAccess, IteratorAggregate, Countable
{

  private array $orgKeyData;

  public function __construct(OrgKeyData ...$orgKeyData)
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

class UsiSettings
{
  public readonly UsiVersionCollection $Versions;

  public function __construct(UsiVersionCollection $versions)
  {
    $this->Versions = $versions;
  }
}

class UsiVersion
{
  public readonly string $Name;
  public readonly string $Url;

  public function __construct(string $name, string $url)
  {
    $this->Name = $name;
    $this->Url = $url;
  }
}

class UsiVersionCollection implements ArrayAccess, IteratorAggregate, Countable
{

  private array $usiVersions;

  public function __construct(UsiVersion ...$usiVersions)
  {
    $this->usiVersions = $usiVersions;
  }

  public function offsetExists(mixed $offset): bool
  {
    return isset($this->usiVersions[$offset]);
  }

  public function offsetGet(mixed $offset): mixed
  {
    return $this->usiVersions[$offset];
  }

  public function offsetSet(mixed $offset, mixed $value): void
  {
    if ($value instanceof UsiVersion) {
      $this->usiVersions[$offset] = $value;
    } else {
      throw new TypeError("Not a UsiVersion object.");
    }
  }

  public function offsetUnset(mixed $offset): void
  {
    unset($this->usiVersions[$offset]);
  }

  public function getIterator(): Traversable
  {
    return new ArrayIterator($this->usiVersions);
  }

  public function count(): int
  {
    return count($this->usiVersions);
  }
}

class Configuration
{
  public readonly string $Environment;
  public readonly UsiSettings $Usi;
  public readonly StsSettings $Sts;
  public readonly string $UsiServiceUrl;
  public readonly string $DefaultOrgCode;
  public readonly string $DefaultVersion;
  public readonly KeyStore $KeyStore;

  public function __construct(string $environment, string $usiServiceUrl, string $defaultOrgCode, string $defaultVersion, StsSettings $sts, KeyStore $keyStore, UsiSettings $usi)
  {
    $this->Environment = $environment;
    $this->Sts = $sts;
    $this->Usi = $usi;
    $this->UsiServiceUrl = $usiServiceUrl;
    $this->DefaultOrgCode = $defaultOrgCode;
    $this->DefaultVersion = $defaultVersion;
    $this->KeyStore = $keyStore;
  }

  public function getOrgKeyData(string $orgCode): OrgKeyData
  {
    foreach ($this->KeyStore->Credentials as $credential) {
      if (strcasecmp($credential->Code, $orgCode) === 0) {
        return $credential;
      }
    }
    throw new \Exception("OrgKeyData for orgCode '{$orgCode}' not found.");
  }

  public function getUsiVersion(string $versionName): UsiVersion
  {
    foreach ($this->Usi->Versions as $version) {
      if (strcasecmp($version->Name, $versionName) === 0) {
        return $version;
      }
    }
    throw new \Exception("UsiVersion for versionName '{$versionName}' not found.");
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
