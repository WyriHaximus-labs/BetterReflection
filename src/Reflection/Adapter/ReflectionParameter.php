<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflection\Adapter;

use ReflectionAttribute as CoreReflectionAttribute;
use ReflectionClass as CoreReflectionClass;
use ReflectionFunctionAbstract as CoreReflectionFunctionAbstract;
use ReflectionParameter as CoreReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionMethod as BetterReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter as BetterReflectionParameter;

use function assert;

class ReflectionParameter extends CoreReflectionParameter
{
    public function __construct(private BetterReflectionParameter $betterReflectionParameter)
    {
    }

    public function __toString(): string
    {
        return $this->betterReflectionParameter->__toString();
    }

    public function getName(): string
    {
        return $this->betterReflectionParameter->getName();
    }

    public function isPassedByReference(): bool
    {
        return $this->betterReflectionParameter->isPassedByReference();
    }

    public function canBePassedByValue(): bool
    {
        return $this->betterReflectionParameter->canBePassedByValue();
    }

    public function getDeclaringFunction(): CoreReflectionFunctionAbstract
    {
        $function = $this->betterReflectionParameter->getDeclaringFunction();
        assert($function instanceof BetterReflectionMethod || $function instanceof \Roave\BetterReflection\Reflection\ReflectionFunction);

        if ($function instanceof BetterReflectionMethod) {
            return new ReflectionMethod($function);
        }

        return new ReflectionFunction($function);
    }

    public function getDeclaringClass(): ?CoreReflectionClass
    {
        $declaringClass = $this->betterReflectionParameter->getDeclaringClass();

        if ($declaringClass === null) {
            return null;
        }

        return new ReflectionClass($declaringClass);
    }

    public function getClass(): ?CoreReflectionClass
    {
        $class = $this->betterReflectionParameter->getClass();

        if ($class === null) {
            return null;
        }

        return new ReflectionClass($class);
    }

    public function isArray(): bool
    {
        return $this->betterReflectionParameter->isArray();
    }

    public function isCallable(): bool
    {
        return $this->betterReflectionParameter->isCallable();
    }

    public function allowsNull(): bool
    {
        return $this->betterReflectionParameter->allowsNull();
    }

    public function getPosition(): int
    {
        return $this->betterReflectionParameter->getPosition();
    }

    public function isOptional(): bool
    {
        return $this->betterReflectionParameter->isOptional();
    }

    public function isVariadic(): bool
    {
        return $this->betterReflectionParameter->isVariadic();
    }

    public function isDefaultValueAvailable(): bool
    {
        return $this->betterReflectionParameter->isDefaultValueAvailable();
    }

    public function getDefaultValue(): mixed
    {
        return $this->betterReflectionParameter->getDefaultValue();
    }

    public function isDefaultValueConstant(): bool
    {
        return $this->betterReflectionParameter->isDefaultValueConstant();
    }

    public function getDefaultValueConstantName(): string
    {
        return $this->betterReflectionParameter->getDefaultValueConstantName();
    }

    public function hasType(): bool
    {
        return $this->betterReflectionParameter->hasType();
    }

    public function getType(): ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|ReflectionType|null
    {
        return ReflectionType::fromTypeOrNull($this->betterReflectionParameter->getType());
    }

    public function isPromoted(): bool
    {
        return $this->betterReflectionParameter->isPromoted();
    }

    /**
     * @phpcs:disable Squiz.Commenting.FunctionComment.InvalidNoReturn
     * @return list<CoreReflectionAttribute>
     */
    public function getAttributes(?string $name = null, int $flags = 0): array
    {
        throw new Exception\NotImplemented('Not implemented');
    }
}
