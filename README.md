# Arash Settings Framework

Production-ready reusable WordPress settings framework for plugins and themes.

Build polished admin settings pages from PHP configuration arrays — tabs, conditionals, repeaters, media, color, and editors — with a small public API and strict server-side validation.

## Requirements

- PHP **7.4+**
- WordPress **5.9+**
- No runtime Composer dependency (Composer is optional for tests)

## Installation

1. Copy this repository into your plugin (e.g. `includes/arash-settings-framework/`).
2. `require_once` `arash-settings-framework.php`.
3. Register pages/tabs with `Arash\SettingsFramework\Settings`.

Or activate this folder as a plugin and use the example under `examples/asf-example-plugin/`.

See [docs/getting-started.md](docs/getting-started.md).

## Quick start

```php
use Arash\SettingsFramework\Settings;

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

$value = asf_get_option('my_plugin_settings', 'enable_feature', false);
```

## Public API

| API | Purpose |
|-----|---------|
| `Settings::register_page( array $config )` | Register admin page |
| `Settings::register_tab( string $page_id, array $config )` | Register tab + fields |
| `Settings::register_field_type( string $type, string $class )` | Custom field class |
| `asf_get_option( $page, $field, $default = null )` | Read one value |
| `asf_get_options( $page )` | Read all (merged with defaults) |
| `asf_attachment_url( $id, $size = 'full' )` | Resolve media ID → URL |
| `asf_framework()` | Framework singleton |
| `asf_loader_info()` | Multi-instance diagnostics |

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

**First loaded copy wins.** Later bundles of the same major version are skipped. Incompatible majors are reported when `WP_DEBUG` is enabled. ASF does not hot-swap implementations after boot.

Prefixes: PHP namespace `Arash\SettingsFramework`, functions `asf_*`, assets `asf-`, text domain `arash-settings-framework`.

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
