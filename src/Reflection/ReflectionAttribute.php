<?php

declare(strict_types=1);

namespace Roave\BetterReflection\Reflection;

use Attribute;
use PhpParser\Node;
use Roave\BetterReflection\NodeCompiler\CompileNodeToValue;
use Roave\BetterReflection\NodeCompiler\CompilerContext;
use Roave\BetterReflection\Reflector\Reflector;

class ReflectionAttribute
{
    public function __construct(
        private Reflector $reflector,
        private Node\Attribute $node,
        private ReflectionClass|ReflectionMethod|ReflectionFunction|ReflectionClassConstant|ReflectionProperty|ReflectionParameter $owner,
        private bool $isRepeated,
    ) {
    }

    public function getName(): string
    {
        return $this->node->name->toString();
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getArguments(): array
    {
        $arguments = [];

        $compiler = new CompileNodeToValue();
        $context  = new CompilerContext($this->reflector, $this->owner);

        foreach ($this->node->args as $argNo => $arg) {
            $arguments[$arg->name?->toString() ?? $argNo] = $compiler->__invoke($arg->value, $context);
        }

        return $arguments;
    }

    public function getTarget(): int
    {
        return match (true) {
            $this->owner instanceof ReflectionClass => Attribute::TARGET_CLASS,
            $this->owner instanceof ReflectionFunction => Attribute::TARGET_FUNCTION,
            $this->owner instanceof ReflectionMethod => Attribute::TARGET_METHOD,
            $this->owner instanceof ReflectionProperty => Attribute::TARGET_PROPERTY,
            $this->owner instanceof ReflectionClassConstant => Attribute::TARGET_CLASS_CONSTANT,
            $this->owner instanceof ReflectionParameter => Attribute::TARGET_PARAMETER,
        };
    }

    public function isRepeated(): bool
    {
        return $this->isRepeated;
    }
}