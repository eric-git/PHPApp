<?php

declare(strict_types=1);

namespace Usi\Models;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\BaseViewModel.php");

use ArrayAccess;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use Countable;
use TypeError;

class OperationsViewModel extends BaseViewModel
{
    public OperationCollection $Operations;
}

class Operation
{
    public readonly string $Name;
    public readonly string $Signature;

    function __construct(string $signature)
    {
        $this->Signature = $signature;
        $matches = array();
        preg_match("/.+\s+(.+)\(.+\)/", $signature, $matches);
        $this->Name = $matches[1];
    }
}

class OperationCollection implements ArrayAccess, IteratorAggregate, Countable
{

    private array $operations;

    function __construct(Operation ...$operations)
    {
        $this->operations = $operations;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->operations[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->operations[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof Operation) {
            $this->operations[$offset] = $value;
        } else {
            throw new TypeError("Not a Operation object.");
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->operations[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->operations);
    }

    public function count(): int
    {
        return count($this->operations);
    }
}
