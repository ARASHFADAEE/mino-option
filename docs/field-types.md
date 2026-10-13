# Field types

Field IDs must match `/^[a-z][a-z0-9_]{0,63}$/`.

Common properties: `id`, `type`, `title`, `description`, `default`, `required`, `disabled`, `readonly`, `placeholder`, `options`, `attributes`, `class`, `require`, `sanitize`, `validate`, `renderer`.

Unsupported properties are ignored. Invalid IDs throw during registration (surfaced as admin notices).

---

## text

- **Sanitize:** `sanitize_text_field`
- **Stored:** string
- **Example:** `['id'=>'site_name','type'=>'text','title'=>'Site Name','default'=>'']`

## email

- **Sanitize:** `sanitize_email` + `is_email` (invalid → `''`)
- **Stored:** string

## url

- **Sanitize:** `esc_url_raw` (invalid → `''`)
- **Stored:** string

## password

- **Sanitize:** `sanitize_text_field`
- **Stored:** string
- **Note:** Avoid echoing secrets into HTML attributes beyond the input value itself; do not log password fields.

## number

- **Sanitize:** int, or float when `step` contains `.`
- **Extras:** `min`, `max`, `step`
- **Stored:** int|float|null

## textarea

- **Sanitize:** `sanitize_textarea_field`
- **Extras:** `rows`
- **Stored:** string

## hidden

- **Sanitize:** `sanitize_text_field`
- **Stored:** string
- Use only when justified; still allowlisted.

## checkbox / switch (alias: switcher)

- **Sanitize:** boolean (`Sanitizer::boolean`)
- **Stored:** bool
- Absent from POST on save → `false`

## select / radio / buttonset

- **Sanitize:** allowlisted against `options` keys
- **Stored:** string

## multiselect

- **Sanitize:** allowlisted array
- **Stored:** `string[]`

## color

- **Sanitize:** `sanitize_hex_color` (invalid → `''`)
- **Assets:** `wp-color-picker`
- **Stored:** string (`#rrggbb`)

## image / media

- **Sanitize:** attachment ID via `get_post` type check (invalid → `0`)
- **Stored:** int
- **Helper:** `asf_attachment_url( $id, $size )`
- **Extras:** `library` (`image`, `all`, …)

## gallery

- **Sanitize:** array of valid attachment IDs
- **Stored:** `int[]`
- Form posts a comma-separated string; save handler expands it.

## editor (aliases: tinymce, richtext)

- **Sanitize:** `wp_kses_post`
- **Stored:** string (HTML)
- **Extras:** `rows`, `media_buttons`, `teeny`

## repeater

- **Sanitize:** recursive per subfield; min/max rows enforced
- **Stored:** `array<int, array<string, mixed>>`
- **Extras:** `fields`, `min_rows`, `max_rows`
- Supports add / remove / duplicate / sortable rows

## heading / notice / html

- Non-persistent (not saved)
- `html` requires `'trusted' => true` to render unescaped markup

## callback

- **Render:** `callback` or `renderer` callable
- **Sanitize:** custom `sanitize` callable or text fallback

---

## Conditional rules (`require`)

```php
'require' => [
    'relation' => 'AND', // or OR
    ['enable_feature', '==', true],
    ['mode', 'in', ['live', 'sandbox']],
],
```

Operators: `==`, `!=`, `empty`, `not_empty`, `in`, `not_in`.
