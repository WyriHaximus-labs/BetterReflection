<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\Reflection;

use Attribute;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Roave\BetterReflectionTest\BetterReflectionSingleton;
use Roave\BetterReflectionTest\Fixture\AnotherAttr;
use Roave\BetterReflectionTest\Fixture\Attr;
use Roave\BetterReflectionTest\Fixture\ClassWithAttributes;
use Roave\BetterReflectionTest\Fixture\ClassWithAttributesWithArguments;
use Roave\BetterReflectionTest\Fixture\ClassWithRepeatedAttributes;

/**
 * @covers \Roave\BetterReflection\Reflection\ReflectionAttribute
 */
class ReflectionAttributeTest extends TestCase
{
    private Locator $astLocator;
    private Reflector $reflector;

    public function setUp(): void
    {
        parent::setUp();

        $this->astLocator = BetterReflectionSingleton::instance()->astLocator();
        $this->reflector  = new Reflector(new SingleFileSourceLocator(__DIR__ . '/../Fixture/Attributes.php', $this->astLocator));
    }

    public function testAttributes(): void
    {
        $classReflection = $this->reflector->reflectClass(ClassWithAttributes::class);
        $attributes      = $classReflection->getAttributes();

        self::assertCount(2, $attributes);
    }

    public function testRepeatedAttributes(): void
    {
        $classReflection = $this->reflector->reflectClass(ClassWithRepeatedAttributes::class);

        $notRepeatedAttributes = $classReflection->getAttributesByName(Attr::class);
        self::assertCount(1, $notRepeatedAttributes);
        self::assertFalse($notRepeatedAttributes[0]->isRepeated());

        $repeatedAttributes = $classReflection->getAttributesByName(AnotherAttr::class);
        self::assertCount(2, $repeatedAttributes);
        self::assertTrue($repeatedAttributes[0]->isRepeated());
    }

    public function testGetArgumentsWhenNoArguments(): void
    {
        $classReflection = $this->reflector->reflectClass(ClassWithAttributes::class);
        $attributes      = $classReflection->getAttributesByName(Attr::class);

        self::assertCount(1, $attributes);
        self::assertCount(0, $attributes[0]->getArguments());
    }

    public function testGetArgumentsWithArguments(): void
    {
        $classReflection = $this->reflector->reflectClass(ClassWithAttributesWithArguments::class);
        $attributes      = $classReflection->getAttributesByName(Attr::class);

        self::assertCount(1, $attributes);

        $expectedArguments = [
            0 => 'arg1',
            1 => 'arg2',
            'arg3' => ClassWithAttributesWithArguments::class,
            'arg4' => [
                0 => 0,
                1 => ClassWithAttributes::class,
                2 => [
                    ClassWithAttributesWithArguments::class,
                    ClassWithRepeatedAttributes::class,
                ],
            ],
        ];

        self::assertSame($expectedArguments, $attributes[0]->getArguments());
    }

    public function testGetTargetWithClass(): void
    {
        $classReflection = $this->reflector->reflectClass(ClassWithAttributes::class);
        $attributes      = $classReflection->getAttributes();

        self::assertNotEmpty($attributes);
        self::assertSame(Attribute::TARGET_CLASS, $attributes[0]->getTarget());
    }

    public function testGetTargetWithClassConstant(): void
    {
        $classReflection    = $this->reflector->reflectClass(ClassWithAttributes::class);
        $constantReflection = $classReflection->getReflectionConstant('CONSTANT_WITH_ATTRIBUTES');
        $attributes         = $constantReflection->getAttributes();

        self::assertNotEmpty($attributes);
        self::assertSame(Attribute::TARGET_CLASS_CONSTANT, $attributes[0]->getTarget());
    }

    public function testGetTargetWithProperty(): void
    {
        $classReflection    = $this->reflector->reflectClass(ClassWithAttributes::class);
        $propertyReflection = $classReflection->getProperty('propertyWithAttributes');
        $attributes         = $propertyReflection->getAttributes();

        self::assertNotEmpty($attributes);
        self::assertSame(Attribute::TARGET_PROPERTY, $attributes[0]->getTarget());
    }

    public function testGetTargetWithMethod(): void
    {
        $classReflection  = $this->reflector->reflectClass(ClassWithAttributes::class);
        $methodReflection = $classReflection->getMethod('methodWithAttributes');
        $attributes       = $methodReflection->getAttributes();

        self::assertNotEmpty($attributes);
        self::assertSame(Attribute::TARGET_METHOD, $attributes[0]->getTarget());
    }

    public function testGetTargetWithParameter(): void
    {
        $classReflection     = $this->reflector->reflectClass(ClassWithAttributes::class);
        $methodReflection    = $classReflection->getMethod('methodWithAttributes');
        $parameterReflection = $methodReflection->getParameter('parameterWithAttributes');
        $attributes          = $parameterReflection->getAttributes();

        self::assertNotEmpty($attributes);
        self::assertSame(Attribute::TARGET_PARAMETER, $attributes[0]->getTarget());
    }

    public function testGetTargetWithFunction(): void
    {
        $functionReflection = $this->reflector->reflectFunction('Roave\BetterReflectionTest\Fixture\functionWithAttributes');
        $attributes         = $functionReflection->getAttributes();

        self::assertNotEmpty($attributes);
        self::assertSame(Attribute::TARGET_FUNCTION, $attributes[0]->getTarget());
    }
}
