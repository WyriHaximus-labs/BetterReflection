<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflection;

use PhpParser\Node\UnionType;

use function array_map;
use function implode;

class ReflectionUnionType
{
    /** @var non-empty-list<ReflectionNamedType|ReflectionUnionType> */
    private array $types;

    private bool $allowsNull;

    public function __construct(UnionType $type, bool $allowsNull)
    {
        $this->types = array_map(static function ($type): ReflectionNamedType|ReflectionUnionType {
            return ReflectionType::createFromTypeAndReflector($type);
        }, $type->types);
        $this->allowsNull = $allowsNull;
    }

    /**
     * @return non-empty-list<ReflectionNamedType|ReflectionUnionType>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Does the parameter allow null?
     */
    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    public function __toString(): string
    {
        return implode('|', array_map(static fn (ReflectionNamedType|ReflectionUnionType $type): string => $type->__toString(), $this->types));
    }
}
