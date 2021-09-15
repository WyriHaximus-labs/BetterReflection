<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflection;

use PhpParser\Node\UnionType;

use function array_map;
use function implode;

class ReflectionUnionType extends ReflectionType
{
    /** @var non-empty-list<ReflectionType> */
    private array $types;

    public function __construct(UnionType $type, bool $allowsNull)
    {
        parent::__construct($allowsNull);
        $this->types = array_map(static function ($type): ReflectionType {
            return ReflectionType::createFromTypeAndReflector($type);
        }, $type->types);
    }

    /**
     * @return non-empty-list<ReflectionType>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function __toString(): string
    {
        return implode('|', array_map(static fn (ReflectionType $type): string => $type->__toString(), $this->types));
    }
}
