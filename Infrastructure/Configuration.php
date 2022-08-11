<?php

declare(strict_types=1);

namespace Usi\Infrastructure;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use Traversable;
use TypeError;
use ArrayIterator;

class StsSettings
{
    public readonly string $IssuerUrl;
    public readonly string $AppliesTo;

    function __construct(string $issuerUrl, string $appliesTo)
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

    function __construct(string $url, int $port, string $username = null, string $password = null)
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

    function __construct(string $environment, StsSettings $sts, string $usiServiceUrl, string $defaultOrgCode, ProxySettings $proxy = null)
    {
        $this->Environment = $environment;
        $this->Sts = $sts;
        $this->UsiServiceUrl = $usiServiceUrl;
        $this->DefaultOrgCode = $defaultOrgCode;
        $this->Proxy = $proxy;
    }
}

class ConfigurationCollection implements ArrayAccess, IteratorAggregate, Countable
{

    private array $configurations;

    function __construct(Configuration ...$configurations)
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
