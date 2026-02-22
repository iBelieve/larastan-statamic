<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\ReturnTypes;

use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ReturnTypeExtensionTest extends TypeInferenceTestCase
{
    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__.'/../Fixtures/Rules/rules-test.neon',
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__.'/../Fixtures/ReturnTypes/EntryFacadeReturnTypes.php');
        yield from self::gatherAssertTypes(__DIR__.'/../Fixtures/ReturnTypes/CollectionFindReturnTypes.php');
        yield from self::gatherAssertTypes(__DIR__.'/../Fixtures/ReturnTypes/GlobalSetFindReturnTypes.php');
        yield from self::gatherAssertTypes(__DIR__.'/../Fixtures/ReturnTypes/EntryQueryBuilderReturnTypes.php');
    }

    #[DataProvider('dataFileAsserts')]
    public function test_file_asserts(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }
}
