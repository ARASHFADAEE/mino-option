# Security model

## Trust boundaries

| Boundary | Trust |
|----------|--------|
| PHP registration config from the developer | Trusted (code) |
| `$_GET['page']`, `$_GET['mino_tab']` | Untrusted — sanitized / allowlisted |
| `$_POST` field values | Untrusted — sanitized per type |
| Custom tab `callback` | Trusted (developer code) |
| Custom tab `template` | Trusted only inside `template_base` realpath |
| Field `html` content | Escaped unless `trusted => true` |
| Custom `renderer` / `sanitize` / `validate` callables | Trusted (developer code) |

## Authorization

- Page render: `current_user_can( $page['capability'] )` (filterable via `mino_user_can`).
- Save/reset: capability **and** nonce (`mino_nonce` / `mino_save_settings_{page_id}`).
- Missing capability or nonce → `wp_die` 403 (no partial write).

## Persistence hardening

- Only registered field IDs are written.
- Select/radio/buttonset/multiselect values are allowlisted.
- Media/gallery IDs must reference real attachments.
- Repeaters recursively sanitize subfields and reject non-array rows.
- Display-only types never persist.

## Output escaping

- Labels, titles, descriptions: `esc_html`
- Attributes: `esc_attr`
- URLs: `esc_url`
- Editor content on save: `wp_kses_post`
- Template paths: never from raw user input without allowlist

## CSRF

WordPress nonces on every settings form (`mino_nonce` / `mino_save_settings_{page_id}`).

Primary write path is `admin-ajax.php` action `mino_save_settings` (capability + nonce). Classic POST + redirect remains as a no-JS fallback and reuses the same `SaveHandler::process_request()` pipeline.

## XSS

Framework markup escapes by default. Rich text goes through `wp_kses_post`. Trusted HTML is opt-in.

## Secrets

Do not store API secrets in field defaults that render into the page unnecessarily. Password fields still appear in HTML as values when editing — treat admin capability as the access boundary. Never log field payloads.

## Custom includes

`RequestGuard::resolve_template()` requires `realpath` under `template_base`. Path traversal (`../`) fails closed.
