<?php

declare(strict_types=1);

namespace Roave\BetterReflectionTest\SourceLocator\Type;

use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Ast\Parser\MemoizingParser;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflectionTest\Fixture\ExampleClass;

use function class_exists;

/** @covers \Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator */
class AutoloadSourceLocatorWithoutLoadedParserDependenciesTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCanFindClassEvenWhenParserIsNotLoadedInMemory(): void
    {
        self::assertFalse(
            class_exists(MemoizingParser::class, false),
            MemoizingParser::class . ' was not loaded into memory',
        );

        $parser        = (new ParserFactory())->create(ParserFactory::ONLY_PHP7, new Emulative([
            'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
        ]));
        $sourceLocator = new AutoloadSourceLocator(
            new Locator($parser),
            $parser,
        );

        $reflector  = new Reflector($sourceLocator);
        $reflection = $reflector->reflectClass(ExampleClass::class);

        self::assertSame(ExampleClass::class, $reflection->getName());
        self::assertFalse(
            class_exists(MemoizingParser::class, false),
            MemoizingParser::class . ' was not implicitly loaded',
        );
    }
}
