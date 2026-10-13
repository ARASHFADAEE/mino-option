=== Arash Settings Framework ===
Contributors: arashfadaee
Tags: settings, options, framework, admin, fields
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Reusable WordPress settings framework for plugins and themes.

== Description ==

Arash Settings Framework lets developers register polished admin settings pages with tabs, conditional fields, repeaters, media, color pickers, and editors using PHP configuration arrays.

This plugin can run standalone for development, or be bundled inside other plugins/themes. It does not register demo settings by itself.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/arash-settings-framework/`
2. Activate through the Plugins screen, or `require` the bootstrap from another plugin
3. Register pages with `Arash\SettingsFramework\Settings`

== Frequently Asked Questions ==

= Does it create demo options? =

No. Use the example plugin under `examples/asf-example-plugin/`.

= Can multiple plugins bundle it? =

Yes. The first loaded copy boots; later copies are skipped. See the README compatibility section.

== Changelog ==

= 1.0.0 =
* Initial public release
