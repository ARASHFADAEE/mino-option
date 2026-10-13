# Extensibility

## Custom field types

```php
use Arash\SettingsFramework\Fields\AbstractField;
use Arash\SettingsFramework\Settings;

final class MySlugField extends AbstractField {
    public static function type(): string { return 'slug'; }
    public function sanitize( $value, array $field ) {
        return sanitize_title( (string) $value );
    }
    public function render( array $field, $value, array $context ): void {
        printf(
            '<input type="text" class="asf-input" id="%1$s" name="%2$s" value="%3$s" />',
            esc_attr( $context['id'] ),
            esc_attr( $context['name'] ),
            esc_attr( (string) $value )
        );
    }
}

Settings::register_field_type( 'slug', MySlugField::class );
```

## Filters / actions (timing)

| Hook | When |
|------|------|
| `asf_booted` | After framework hooks registered |
| `asf_register_page` | Before page stored |
| `asf_page_registered` | After page stored |
| `asf_register_tab` | Before tab stored |
| `asf_normalize_field` | Per field during tab registration |
| `asf_page_fields` | When resolving field map |
| `asf_page_defaults` | When resolving defaults |
| `asf_get_option` / `asf_get_options` | On read |
| `asf_sanitize_field` / `asf_sanitize_page` | On save sanitize |
| `asf_validate_page` | After sanitize, before persist |
| `asf_pre_save_options` | Immediately before `update_option` |
| `asf_saved` | After successful save |
| `asf_reset` | After reset |
| `asf_render_field` | After field HTML built |
| `asf_field_attributes` | Control attributes |
| `asf_enqueue_assets` | After assets enqueued |
| `asf_user_can` | Capability gate |
| `asf_editor_settings` | `wp_editor` settings |

## Custom CSS classes

Use field `class`, or filter `asf_render_field` / `asf_field_attributes`. Keep styles under `.asf-settings` when adding CSS.
