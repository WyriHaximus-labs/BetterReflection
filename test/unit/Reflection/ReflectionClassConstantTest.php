<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\Reflection;

use PhpParser\Node\Stmt\ClassConst;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant as CoreReflectionClassConstant;
use Roave\BetterReflection\Reflection\ReflectionClassConstant;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\StringSourceLocator;
use Roave\BetterReflectionTest\BetterReflectionSingleton;
use Roave\BetterReflectionTest\Fixture\Attr;
use Roave\BetterReflectionTest\Fixture\ClassWithAttributes;
use Roave\BetterReflectionTest\Fixture\ExampleClass;

class ReflectionClassConstantTest extends TestCase
{
    private Locator $astLocator;

    public function setUp(): void
    {
        parent::setUp();

        $this->astLocator = BetterReflectionSingleton::instance()->astLocator();
    }

    private function getComposerLocator(): ComposerSourceLocator
    {
        return new ComposerSourceLocator(
            require __DIR__ . '/../../../vendor/autoload.php',
            $this->astLocator,
        );
    }

    private function getExampleConstant(string $name): ?ReflectionClassConstant
    {
        $reflector = new Reflector($this->getComposerLocator());
        $classInfo = $reflector->reflectClass(ExampleClass::class);

        return $classInfo->getReflectionConstant($name);
    }

    public function testDefaultVisibility(): void
    {
        $const = $this->getExampleConstant('MY_CONST_1');
        self::assertTrue($const->isPublic());
    }

    public function testPublicVisibility(): void
    {
        $const = $this->getExampleConstant('MY_CONST_3');
        self::assertTrue($const->isPublic());
    }

    public function testProtectedVisibility(): void
    {
        $const = $this->getExampleConstant('MY_CONST_4');
        self::assertTrue($const->isProtected());
    }

    public function testPrivateVisibility(): void
    {
        $const = $this->getExampleConstant('MY_CONST_5');
        self::assertTrue($const->isPrivate());
    }

    public function testToString(): void
    {
        self::assertSame("Constant [ public integer MY_CONST_1 ] { 123 }\n", (string) $this->getExampleConstant('MY_CONST_1'));
    }

    /**
     * @dataProvider getModifiersProvider
     */
    public function testGetModifiers(string $const, int $expected): void
    {
        self::assertSame($expected, $this->getExampleConstant($const)->getModifiers());
    }

    public function getModifiersProvider(): array
    {
        return [
            ['MY_CONST_1', CoreReflectionClassConstant::IS_PUBLIC],
            ['MY_CONST_3', CoreReflectionClassConstant::IS_PUBLIC],
            ['MY_CONST_4', CoreReflectionClassConstant::IS_PROTECTED],
            ['MY_CONST_5', CoreReflectionClassConstant::IS_PRIVATE],
        ];
    }

    public function testGetDocComment(): void
    {
        $const = $this->getExampleConstant('MY_CONST_2');
        self::assertStringContainsString('This comment for constant should be used.', $const->getDocComment());
    }

    public function testGetDocCommentReturnsEmptyStringWithNoComment(): void
    {
        $const = $this->getExampleConstant('MY_CONST_1');
        self::assertSame('', $const->getDocComment());
    }

    public function testGetDeclaringClass(): void
    {
        $reflector = new Reflector($this->getComposerLocator());
        $classInfo = $reflector->reflectClass(ExampleClass::class);
        $const     = $classInfo->getReflectionConstant('MY_CONST_1');
        self::assertSame($classInfo, $const->getDeclaringClass());
    }

    /**
     * @dataProvider startEndLineProvider
     */
    public function testStartEndLine(string $php, int $startLine, int $endLine): void
    {
        $reflector       = new Reflector(new StringSourceLocator($php, $this->astLocator));
        $classReflection = $reflector->reflectClass('\T');
        $constReflection = $classReflection->getReflectionConstant('TEST');
        self::assertEquals($startLine, $constReflection->getStartLine());
        self::assertEquals($endLine, $constReflection->getEndLine());
    }

    public function startEndLineProvider(): array
    {
        return [
            ["<?php\nclass T {\nconst TEST = 1; }", 3, 3],
            ["<?php\n\nclass T {\nconst TEST = 1; }", 4, 4],
            ["<?php\nclass T {\nconst TEST = \n1; }", 3, 4],
            ["<?php\nclass T {\nconst \nTEST = 1; }", 3, 4],
        ];
    }

    public function columnsProvider(): array
    {
        return [
            ["<?php\n\nclass T {\nconst TEST = 1;}", 1, 15],
            ["<?php\n\n    class T {\n        const TEST = 1;}", 9, 23],
            ['<?php class T {const TEST = 1;}', 16, 30],
        ];
    }

    /**
     * @dataProvider columnsProvider
     */
    public function testGetStartColumnAndEndColumn(string $php, int $startColumn, int $endColumn): void
    {
        $reflector          = new Reflector(new StringSourceLocator($php, $this->astLocator));
        $classReflection    = $reflector->reflectClass('T');
        $constantReflection = $classReflection->getReflectionConstant('TEST');

        self::assertEquals($startColumn, $constantReflection->getStartColumn());
        self::assertEquals($endColumn, $constantReflection->getEndColumn());
    }

    public function getAstProvider(): array
    {
        return [
            ['TEST', 0],
            ['TEST2', 1],
        ];
    }

    /**
     * @dataProvider getAstProvider
     */
    public function testGetAst(string $constantName, int $positionInAst): void
    {
        $php = <<<'PHP'
<?php
class Foo
{
    const TEST = 'test',
        TEST2 = 'test2';
}
PHP;

        $reflector          = new Reflector(new StringSourceLocator($php, $this->astLocator));
        $classReflection    = $reflector->reflectClass('Foo');
        $constantReflection = $classReflection->getReflectionConstant($constantName);

        $ast = $constantReflection->getAst();

        self::assertInstanceOf(ClassConst::class, $ast);
        self::assertSame($positionInAst, $constantReflection->getPositionInAst());
        self::assertSame($constantName, $ast->consts[$positionInAst]->name->name);
    }

    public function testGetAttributesWithoutAttributes(): void
    {
        $reflector          = new Reflector(new SingleFileSourceLocator(__DIR__ . '/../Fixture/ExampleClass.php', $this->astLocator));
        $classReflection    = $reflector->reflectClass(ExampleClass::class);
        $constantReflection = $classReflection->getReflectionConstant('MY_CONST_1');
        $attributes         = $constantReflection->getAttributes();

        self::assertCount(0, $attributes);
    }

    public function testGetAttributesWithAttributes(): void
    {
        $reflector          = new Reflector(new SingleFileSourceLocator(__DIR__ . '/../Fixture/Attributes.php', $this->astLocator));
        $classReflection    = $reflector->reflectClass(ClassWithAttributes::class);
        $constantReflection = $classReflection->getReflectionConstant('CONSTANT_WITH_ATTRIBUTES');
        $attributes         = $constantReflection->getAttributes();

        self::assertCount(2, $attributes);
    }

    public function testGetAttributesByName(): void
    {
        $reflector          = new Reflector(new SingleFileSourceLocator(__DIR__ . '/../Fixture/Attributes.php', $this->astLocator));
        $classReflection    = $reflector->reflectClass(ClassWithAttributes::class);
        $constantReflection = $classReflection->getReflectionConstant('CONSTANT_WITH_ATTRIBUTES');
        $attributes         = $constantReflection->getAttributesByName(Attr::class);

        self::assertCount(1, $attributes);
    }
}
