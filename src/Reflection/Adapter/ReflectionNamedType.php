<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflection\Adapter;

use ReflectionNamedType as CoreReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionNamedType as BetterReflectionNamedType;

class ReflectionNamedType extends CoreReflectionNamedType
{
    private BetterReflectionNamedType $betterReflectionType;

    public function __construct(BetterReflectionNamedType $betterReflectionType)
    {
        $this->betterReflectionType = $betterReflectionType;
    }

    public static function fromReturnTypeOrNull(?BetterReflectionNamedType $betterReflectionType): ?self
    {
        if ($betterReflectionType === null) {
            return null;
        }

        return new self($betterReflectionType);
    }

    public function getName(): string
    {
        return $this->betterReflectionType->getName();
    }

    public function __toString(): string
    {
        return $this->betterReflectionType->__toString();
    }

    public function allowsNull(): bool
    {
        return $this->betterReflectionType->allowsNull();
    }

    public function isBuiltin(): bool
    {
        return $this->betterReflectionType->isBuiltin();
    }
}
