<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

use ArrayAccess;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use Countable;
use TypeError;
use DateTime;

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
