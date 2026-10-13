# Getting started

## Requirements

- PHP **7.4+**
- WordPress **5.9+**
- No Composer runtime dependency

## Install inside a plugin

```text
your-plugin/
  includes/
    arash-settings-framework/   ← copy this repository
  your-plugin.php
```

```php
<?php
// your-plugin.php
require_once __DIR__ . '/includes/arash-settings-framework/arash-settings-framework.php';

use Arash\SettingsFramework\Settings;

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
$on = asf_get_option( 'my_plugin_settings', 'enable_feature', false );
$all = asf_get_options( 'my_plugin_settings' );
```

## Theme integration

Load the bootstrap from the theme (e.g. `get_template_directory() . '/inc/arash-settings-framework/arash-settings-framework.php'`) and register pages the same way. Prefer `menu_type => submenu` under `themes.php` when appropriate.

## Example plugin

See `examples/asf-example-plugin/` — symlink or copy the example into `wp-content/plugins/` and activate it while this repo lives two levels above the example bootstrap path, **or** adjust the require path to your install.

For a typical WordPress install, copy the whole framework into the example’s vendor path or change:

```php
$asf_bootstrap = WP_PLUGIN_DIR . '/arash-settings-framework/arash-settings-framework.php';
```

## Disable nothing by default

The framework does **not** register demo settings unless you load the example plugin.
