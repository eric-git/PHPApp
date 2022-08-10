<?php

namespace Usi\Models;

require_once($_SERVER['DOCUMENT_ROOT'] . "\Models\BaseViewModel.php");

use ArrayAccess;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use Countable;
use TypeError;

class HomeViewModel extends BaseViewModel
{
    public SectionCollection $Sections;
}

class Section
{
    public readonly string $Title;
    public readonly string $SubTitle;
    public readonly string $Description;
    public readonly string $Background;
    public readonly string $ActionText;
    public readonly string $ActionViewName;

    function __construct(string $title, string $subTitle, string $description, string $background, string $actionText, string $actionViewName)
    {
        $this->Title = $title;
        $this->SubTitle = $subTitle;
        $this->Description = $description;
        $this->Background = $background;
        $this->ActionText = $actionText;
        $this->ActionViewName = $actionViewName;
    }
}

class SectionCollection implements ArrayAccess, IteratorAggregate, Countable
{

    private array $sections;

    function __construct(Section ...$sections)
    {
        $this->sections = $sections;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->sections[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->sections[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof Section) {
            $this->sections[$offset] = $value;
        } else {
            throw new TypeError("Not a Section object.");
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->sections[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->sections);
    }

    public function count(): int
    {
        return count($this->sections);
    }
}
