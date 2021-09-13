<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflection\Adapter;

use ReflectionType as CoreReflectionType;
use Roave\BetterReflection\Reflection\ReflectionType as BetterReflectionType;
use Roave\BetterReflection\Reflection\ReflectionNamedType as BetterReflectionNamedType;

class ReflectionType extends CoreReflectionType
{
    private BetterReflectionType $betterReflectionType;

    public function __construct(BetterReflectionType $betterReflectionType)
    {
        $this->betterReflectionType = $betterReflectionType;
    }

    public static function fromReturnTypeOrNull(?BetterReflectionType $betterReflectionType): ?self
    {
        if ($betterReflectionType === null) {
            return null;
        }

        return new self($betterReflectionType);
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
        return $this->betterReflectionType instanceof BetterReflectionNamedType ? $this->betterReflectionType->isBuiltin() : false;
    }
}
