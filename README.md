# Mino Option

**Clean settings framework for WordPress plugins & themes.**

Build polished admin settings pages from PHP configuration arrays — tabs, conditionals, repeaters, media, color, and editors — with a small public API, AJAX save, and strict server-side validation.

| | |
|---|---|
| **Brand** | Mino Option |
| **Package** | `mino/option` |
| **Namespace** | `Mino\Option` |
| **Helpers** | `mino_get_option()`, `mino_get_options()` |
| **Bootstrap** | `mino-option.php` |
| **Text domain** | `mino-option` |

## Requirements

- PHP **7.4+**
- WordPress **5.9+**
- No runtime Composer dependency (Composer is optional for tests)

## Installation

1. Copy this repository into your plugin (e.g. `includes/mino-option/`).
2. `require_once` `mino-option.php`.
3. Register pages/tabs with `Mino\Option\Settings`.

Or activate this folder as a plugin and use the example under `examples/mino-example-plugin/`.

See [docs/getting-started.md](docs/getting-started.md).

## Quick start

```php
use Mino\Option\Settings;

Settings::register_page([
    'id'          => 'my_plugin_settings',
    'title'       => 'My Plugin',
    'menu_title'  => 'Settings',
    'menu_type'   => 'submenu',
    'parent_slug' => 'options-general.php',
    'capability'  => 'manage_options',
]);

Settings::register_tab('my_plugin_settings', [
    'id'     => 'general',
    'title'  => 'General',
    'fields' => [
        [
            'id'      => 'enable_feature',
            'type'    => 'switch',
            'title'   => 'Enable Feature',
            'default' => false,
        ],
        [
            'id'      => 'api_endpoint',
            'type'    => 'url',
            'title'   => 'API Endpoint',
            'require' => [
                ['enable_feature', '==', true],
            ],
        ],
    ],
]);

$value = mino_get_option('my_plugin_settings', 'enable_feature', false);
```

## Public API

| API | Purpose |
|-----|---------|
| `Settings::register_page( array $config )` | Register admin page |
| `Settings::register_tab( string $page_id, array $config )` | Register tab + fields |
| `Settings::register_field_type( string $type, string $class )` | Custom field class |
| `mino_get_option( $page, $field, $default = null )` | Read one value |
| `mino_get_options( $page )` | Read all (merged with defaults) |
| `mino_attachment_url( $id, $size = 'full' )` | Resolve media ID → URL |
| `mino_framework()` | Framework singleton |
| `mino_loader_info()` | Multi-instance diagnostics |

Duplicate page/tab/field IDs are rejected (not silently overwritten).

## Field types

`text`, `email`, `url`, `password`, `number`, `textarea`, `hidden`, `checkbox`, `switch`/`switcher`, `select`, `multiselect`, `radio`, `buttonset`, `color`, `image`, `media`, `gallery`, `editor`/`tinymce`, `repeater`, `heading`, `notice`, `html`, `callback`.

Full schemas: [docs/field-types.md](docs/field-types.md).

## Storage model

- One `update_option` array per page (`option_key`, default = page `id`).
- Defaults apply on **read**, never on registration.
- Unregistered keys are never saved.
- Reset restores registered defaults.
- Boolean `false` and string `"0"` are distinct, valid values.

## Security model

Capability checks, nonces, field allowlisting, type-aware sanitization, escaped output, safe template resolution. Details: [docs/security.md](docs/security.md).

## Extensibility

Custom field classes + documented hooks. See [docs/extensibility.md](docs/extensibility.md).

## Compatibility strategy

**First loaded copy wins.** Later bundles of the same major version are skipped. Incompatible majors are reported when `WP_DEBUG` is enabled. Mino Option does not hot-swap implementations after boot.

Prefixes: PHP namespace `Mino\Option`, functions `mino_*`, assets `mino-`, text domain `mino-option`.

## Testing

```bash
composer install
composer test
# or
composer lint
```

Unit tests use WordPress stubs (no full WP install required). A full WordPress PHPUnit suite is optional; see notes in [docs/architecture.md](docs/architecture.md).

## Documentation

- **[Developer & Agent Guide](docs/developer-guide.md)** — راهنمای کامل استفاده در افزونه/قالب (برای برنامه‌نویس و ایجنت)
- [Getting started](docs/getting-started.md)
- [Architecture](docs/architecture.md)
- [Field types](docs/field-types.md)
- [Conditional fields](docs/conditional-fields.md)
- [Security](docs/security.md)
- [Extensibility](docs/extensibility.md)

## Known limitations

- Nested repeaters (repeater inside repeater) are not implemented in v1.
- Row-local conditional rules inside repeaters are best-effort in JS.
- Editor fields inside repeater rows are not recommended (TinyMCE ID constraints).
- Nested TinyMCE editors inside repeater rows remain unsupported.
- Gallery values are posted as comma-separated IDs.

## License

MIT. Independently implemented; inspired by the DX of [optionino-framework](https://github.com/shokrino/optionino-framework) (MIT) without copying its source.
