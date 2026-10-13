# Extensibility

## Custom field types

```php
use Mino\Option\Fields\AbstractField;
use Mino\Option\Settings;

final class MySlugField extends AbstractField {
    public static function type(): string { return 'slug'; }
    public function sanitize( $value, array $field ) {
        return sanitize_title( (string) $value );
    }
    public function render( array $field, $value, array $context ): void {
        printf(
            '<input type="text" class="mino-input" id="%1$s" name="%2$s" value="%3$s" />',
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
| `mino_booted` | After framework hooks registered |
| `mino_register_page` | Before page stored |
| `mino_page_registered` | After page stored |
| `mino_register_tab` | Before tab stored |
| `mino_normalize_field` | Per field during tab registration |
| `mino_page_fields` | When resolving field map |
| `mino_page_defaults` | When resolving defaults |
| `mino_get_option` / `mino_get_options` | On read |
| `mino_sanitize_field` / `mino_sanitize_page` | On save sanitize |
| `mino_validate_page` | After sanitize, before persist |
| `mino_pre_save_options` | Immediately before `update_option` |
| `mino_saved` | After successful save |
| `mino_reset` | After reset |
| `mino_render_field` | After field HTML built |
| `mino_field_attributes` | Control attributes |
| `mino_enqueue_assets` | After assets enqueued |
| `mino_user_can` | Capability gate |
| `mino_editor_settings` | `wp_editor` settings |

## Custom CSS classes

Use field `class`, or filter `mino_render_field` / `mino_field_attributes`. Keep styles under `.mino-settings` when adding CSS.
