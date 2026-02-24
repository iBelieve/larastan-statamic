<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Integration test that runs PHPStan on the test-project and verifies
 * that blueprint property resolution and strict mode work correctly.
 *
 * This test requires the test-project's dependencies to be installed.
 * It will be skipped automatically if they are not.
 */
final class TestProjectAnalysisTest extends TestCase
{
    private static string $testProjectDir;

    /** @var array<string, mixed>|null */
    private static ?array $analysisResult = null;

    public static function setUpBeforeClass(): void
    {
        self::$testProjectDir = dirname(__DIR__, 2).'/test-project';

        if (! is_file(self::$testProjectDir.'/vendor/bin/phpstan')) {
            self::markTestSkipped('Test project dependencies are not installed. Run "composer install" in the test-project directory.');
        }

        self::$analysisResult = self::runAnalysis();
    }

    public function test_valid_blueprint_accesses_produce_no_errors(): void
    {
        $errors = $this->getFileErrors('app/Services/BlogService.php');

        $this->assertSame([], $errors, sprintf(
            "Expected no errors in BlogService.php, got:\n%s",
            $this->formatErrors($errors),
        ));
    }

    public function test_undefined_entry_fields_are_reported(): void
    {
        $errors = $this->getFileErrors('app/Services/InvalidFieldAccess.php');
        $undefinedFieldErrors = $this->filterByIdentifier($errors, 'statamic.undefinedField');

        $fieldNames = array_map(
            fn (array $error): string => $this->extractFieldName($error['message']),
            $undefinedFieldErrors,
        );

        $this->assertContains('subtitle', $fieldNames);
        $this->assertContains('summary', $fieldNames);
        $this->assertContains('nonexistent_field', $fieldNames);
    }

    public function test_undefined_global_fields_are_reported(): void
    {
        $errors = $this->getFileErrors('app/Services/InvalidFieldAccess.php');
        $undefinedFieldErrors = $this->filterByIdentifier($errors, 'statamic.undefinedField');

        $fieldNames = array_map(
            fn (array $error): string => $this->extractFieldName($error['message']),
            $undefinedFieldErrors,
        );

        $this->assertContains('theme', $fieldNames);
        $this->assertContains('copyright_text', $fieldNames);
    }

    public function test_undefined_term_fields_are_reported(): void
    {
        $errors = $this->getFileErrors('app/Services/InvalidFieldAccess.php');
        $undefinedFieldErrors = $this->filterByIdentifier($errors, 'statamic.undefinedField');

        $fieldNames = array_map(
            fn (array $error): string => $this->extractFieldName($error['message']),
            $undefinedFieldErrors,
        );

        $this->assertContains('seo_title', $fieldNames);
    }

    public function test_invalid_accesses_produce_exactly_six_blueprint_errors(): void
    {
        $errors = $this->getFileErrors('app/Services/InvalidFieldAccess.php');
        $undefinedFieldErrors = $this->filterByIdentifier($errors, 'statamic.undefinedField');

        // 3 entry fields + 2 global fields + 1 term field = 6 blueprint errors
        $this->assertCount(6, $undefinedFieldErrors, sprintf(
            "Expected exactly 6 statamic.undefinedField errors, got:\n%s",
            $this->formatErrors($undefinedFieldErrors),
        ));
    }

    public function test_invalid_accesses_only_produce_expected_error_types(): void
    {
        $errors = $this->getFileErrors('app/Services/InvalidFieldAccess.php');

        $allowedIdentifiers = [
            'statamic.undefinedField', // Our custom rule
            'property.notFound',      // PHPStan's built-in undefined property check
        ];

        $unexpectedErrors = array_filter(
            $errors,
            static fn (array $error): bool => ! in_array($error['identifier'] ?? '', $allowedIdentifiers, true),
        );

        $this->assertSame([], array_values($unexpectedErrors), sprintf(
            "Found unexpected error types:\n%s",
            $this->formatErrors(array_values($unexpectedErrors)),
        ));
    }

    public function test_existing_app_files_produce_no_errors(): void
    {
        $this->assertFileHasNoErrors('app/Providers/AppServiceProvider.php');
        $this->assertFileHasNoErrors('app/Http/Controllers/Controller.php');
    }

    /**
     * @return array<string, mixed>
     */
    private static function runAnalysis(): array
    {
        $command = sprintf(
            'cd %s && vendor/bin/phpstan analyse --error-format=json --no-progress 2>/dev/null',
            escapeshellarg(self::$testProjectDir),
        );

        $output = shell_exec($command);

        if ($output === null || $output === '') {
            self::fail('PHPStan produced no output. Command may have failed to execute.');
        }

        $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($result));

        return $result;
    }

    /**
     * @return list<array{message: string, line: int, ignorable: bool, identifier: string}>
     */
    private function getFileErrors(string $relativePath): array
    {
        $result = self::$analysisResult;
        assert($result !== null);

        $absolutePath = realpath(self::$testProjectDir.'/'.$relativePath);

        if ($absolutePath === false) {
            $this->fail(sprintf('File not found: %s', $relativePath));
        }

        return $result['files'][$absolutePath]['messages'] ?? [];
    }

    /**
     * @param  list<array{message: string, line: int, ignorable: bool, identifier: string}>  $errors
     * @return list<array{message: string, line: int, ignorable: bool, identifier: string}>
     */
    private function filterByIdentifier(array $errors, string $identifier): array
    {
        return array_values(array_filter(
            $errors,
            static fn (array $error): bool => ($error['identifier'] ?? '') === $identifier,
        ));
    }

    private function extractFieldName(string $message): string
    {
        // Message format: "Access to field 'fieldName' on ClassName that is not defined in any blueprint."
        if (preg_match("/Access to field '([^']+)'/", $message, $matches)) {
            return $matches[1];
        }

        return $message;
    }

    private function assertFileHasNoErrors(string $relativePath): void
    {
        $errors = $this->getFileErrors($relativePath);

        $this->assertSame([], $errors, sprintf(
            "Expected no errors in %s, got:\n%s",
            basename($relativePath),
            $this->formatErrors($errors),
        ));
    }

    /**
     * @param  list<array{message: string, line: int, ignorable: bool, identifier: string}>  $errors
     */
    private function formatErrors(array $errors): string
    {
        if ($errors === []) {
            return '(none)';
        }

        return implode("\n", array_map(
            static fn (array $e): string => sprintf('  Line %d: [%s] %s', $e['line'], $e['identifier'] ?? '?', $e['message']),
            $errors,
        ));
    }
}
