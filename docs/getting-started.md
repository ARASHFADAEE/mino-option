# Getting started

برای راهنمای کامل برنامه‌نویس/ایجنت (قوانین، schema، الگوهای افزونه و قالب) ببینید:

**[developer-guide.md](developer-guide.md)**

## Requirements

- PHP **7.4+**
- WordPress **5.9+**
- No Composer runtime dependency

## Install inside a plugin

```text
your-plugin/
  includes/
    mino-option/   ← copy this repository
  your-plugin.php
```

```php
<?php
// your-plugin.php
require_once __DIR__ . '/includes/mino-option/mino-option.php';

use Mino\Option\Settings;

add_action( 'plugins_loaded', function () {
    Settings::register_page([
        'id'         => 'my_plugin_settings',
        'title'      => 'My Plugin',
        'menu_title' => 'Settings',
        'menu_type'  => 'submenu',
        'parent_slug'=> 'options-general.php',
        'capability' => 'manage_options',
    ]);

    Settings::register_tab( 'my_plugin_settings', [
        'id'     => 'general',
        'title'  => 'General',
        'fields' => [
            [
                'id'      => 'enable_feature',
                'type'    => 'switch',
                'title'   => 'Enable Feature',
                'default' => false,
            ],
        ],
    ]);
});
```

## Read values

```php
$on = mino_get_option( 'my_plugin_settings', 'enable_feature', false );
$all = mino_get_options( 'my_plugin_settings' );
```

## Theme integration

Load the bootstrap from the theme (e.g. `get_template_directory() . '/inc/mino-option/mino-option.php'`) and register pages the same way. Prefer `menu_type => submenu` under `themes.php` when appropriate.

## Example plugin

See `examples/mino-example-plugin/` — symlink or copy the example into `wp-content/plugins/` and activate it while this repo lives two levels above the example bootstrap path, **or** adjust the require path to your install.

For a typical WordPress install, copy the whole framework into the example’s vendor path or change:

```php
$mino_bootstrap = WP_PLUGIN_DIR . '/mino-option/mino-option.php';
```

## Disable nothing by default

The framework does **not** register demo settings unless you load the example plugin.
