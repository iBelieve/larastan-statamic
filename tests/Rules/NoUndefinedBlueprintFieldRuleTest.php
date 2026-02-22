<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Tests\Rules;

use IBelieve\LarastanStatamic\Blueprints\BlueprintLocator;
use IBelieve\LarastanStatamic\Blueprints\BlueprintParser;
use IBelieve\LarastanStatamic\Blueprints\BlueprintRepository;
use IBelieve\LarastanStatamic\Rules\NoUndefinedBlueprintFieldRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoUndefinedBlueprintFieldRule>
 */
final class NoUndefinedBlueprintFieldRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        $locator = new BlueprintLocator([
            __DIR__.'/../Fixtures/blueprints',
        ]);
        $parser = new BlueprintParser;
        $repository = new BlueprintRepository($locator, $parser);

        return new NoUndefinedBlueprintFieldRule($repository);
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__.'/../Fixtures/Rules/rules-test.neon',
        ];
    }

    public function test_flags_undefined_fields_on_entry(): void
    {
        $this->analyse([__DIR__.'/../Fixtures/Rules/EntryPropertyAccess.php'], [
            [
                'Access to field \'nonexistent_field\' on Statamic\Entries\Entry that is not defined in any blueprint.',
                23,
            ],
            [
                'Access to field \'does_not_exist\' on Statamic\Entries\Entry that is not defined in any blueprint.',
                28,
            ],
            [
                'Access to field \'nonexistent_field\' on Statamic\Entries\Entry that is not defined in any blueprint.',
                34,
            ],
        ]);
    }

    public function test_flags_undefined_fields_on_global_variables(): void
    {
        $this->analyse([__DIR__.'/../Fixtures/Rules/GlobalVariablesAccess.php'], [
            [
                'Access to field \'nonexistent_global\' on Statamic\Globals\Variables that is not defined in any blueprint.',
                18,
            ],
        ]);
    }

    public function test_flags_undefined_fields_on_term(): void
    {
        $this->analyse([__DIR__.'/../Fixtures/Rules/TermPropertyAccess.php'], [
            [
                'Access to field \'nonexistent_term_field\' on Statamic\Taxonomies\LocalizedTerm that is not defined in any blueprint.',
                18,
            ],
        ]);
    }

    public function test_flags_undefined_fields_on_asset(): void
    {
        $this->analyse([__DIR__.'/../Fixtures/Rules/AssetPropertyAccess.php'], [
            [
                'Access to field \'nonexistent_asset_field\' on Statamic\Assets\Asset that is not defined in any blueprint.',
                23,
            ],
        ]);
    }

    public function test_ignores_non_content_classes(): void
    {
        $this->analyse([__DIR__.'/../Fixtures/Rules/NonContentClassAccess.php'], []);
    }
}
