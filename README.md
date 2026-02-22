# larastan-statamic

A PHPStan/Larastan extension for [Statamic CMS](https://statamic.dev) that provides blueprint-aware static analysis.

## What it does

Statamic uses YAML blueprint files to define content schemas. Fields defined in blueprints are accessed as magic properties (`$entry->title`, `$entry->body`, etc.), which PHPStan can't understand on its own. This extension:

- **Resolves blueprint fields as typed properties** on `Entry`, `Term`, `Asset`, and `Variables` (GlobalSet) classes
- **Maps Statamic fieldtypes to PHP types** (e.g., `toggle` -> `bool`, `date` -> `Carbon\Carbon`, `entries` with `max_items: 1` -> `Entry|null`)
- **Provides return types for query builders** (`Entry::query()->get()` returns `Collection<int, Entry>`)
- **Provides return types for facades** (`Entry::find()` returns `Entry|null`, `GlobalSet::findByHandle()` returns `GlobalSet|null`)
- **Optional strict mode** that warns when accessing fields not defined in any blueprint

## Installation

```bash
composer require --dev ibelieve/larastan-statamic
```

The extension is automatically registered via PHPStan's extension discovery.

## Configuration

Add to your `phpstan.neon`:

```neon
parameters:
    statamic:
        # Paths to your blueprint directories (defaults to resources/blueprints)
        blueprintPaths:
            - resources/blueprints

        # Map custom fieldtype names to PHP class names
        customFieldtypes:
            my_addon_field: App\Fieldtypes\MyAddonValue

        # Enable strict mode to warn about fields not in any blueprint
        strictMode: false
```

## Fieldtype Mappings

| Fieldtype | PHP Type |
|-----------|----------|
| `text`, `textarea`, `markdown`, `slug`, `code`, `color`, `html`, `video`, `template`, `link`, `time`, `yaml` | `string` |
| `toggle` | `bool` |
| `integer`, `range` | `int` |
| `float` | `float` |
| `date` | `Carbon\Carbon` |
| `entries` (max_items: 1) | `Entry\|null` |
| `entries` (multiple) | `array<int, Entry>` |
| `terms` (max_items: 1) | `LocalizedTerm\|null` |
| `terms` (multiple) | `array<int, LocalizedTerm>` |
| `assets` (max_items: 1) | `Asset\|null` |
| `assets` (multiple) | `array<int, Asset>` |
| `users` (max_items: 1) | `User\|null` |
| `users` (multiple) | `array<int, User>` |
| `select`, `button_group`, `radio` | `string\|int` |
| `checkboxes`, `array`, `list`, `bard`, `replicator`, `grid` | `array` |
| Unknown/custom | `mixed` (override via `customFieldtypes`) |

## Requirements

- PHP 8.2+
- PHPStan 2.0+
- Statamic 5.0+
