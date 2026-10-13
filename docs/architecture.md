# Architecture

## Overview

Mino Option (Mino Option) is a namespaced, modular WordPress settings toolkit designed to be bundled inside plugins and themes.

## Component responsibilities

| Component | Responsibility |
|-----------|----------------|
| `mino-option.php` | Multi-instance loader, constants, boot |
| `Framework` | Hook registration (once) |
| `Settings` | Public registration facade |
| `Registry\PageRegistry` | Pages, tabs, fields (in-memory) |
| `Fields\*` | Render + sanitize per type |
| `Fields\FieldTypeRegistry` | Type map + extension |
| `Storage\OptionStore` | Options API persistence |
| `Validation\Sanitizer` / `Validator` | Save pipeline |
| `Security\RequestGuard` | Capability, nonce, safe templates |
| `Admin\*` | Menus, page UI, save handler |
| `Assets\AssetManager` | Conditional enqueue |
| `Support\ConditionalEvaluator` | Server-side dependency rules |

## Bootstrap lifecycle

1. Bundle metadata is pushed into `$GLOBALS['mino_loader_registry']`.
2. **First loaded copy wins.** Later copies are skipped; major mismatches are recorded as conflicts.
3. Autoloader + `functions.php` load; `Framework::boot()` registers hooks once.
4. Consumer plugins call `Settings::register_page()` / `register_tab()` on `plugins_loaded` (or earlier).
5. On `admin_menu`, menus are created from the registry.
6. On `admin_enqueue_scripts`, assets load only when `$_GET['page']` matches a registered slug.
7. On save, the admin UI posts via AJAX (`mino_save_settings`) with a glass loading overlay; `admin_init` still accepts classic POST as a no-JS fallback.

## Multi-instance strategy

**Decision:** first-loaded version locks the runtime.

Why not “highest version wins”?

- Optionino’s approach can swap implementations after another plugin has already registered settings against an older API shape.
- Mino Option refuses to replace a booted runtime. Incompatible majors surface under `WP_DEBUG`.

Trade-off: an older copy loaded first keeps running. Document that host plugins should load Mino Option early, or ship a single shared mu-plugin copy when possible.

## Persistence

- One WordPress option array per page (`option_key`, defaulting to page `id`).
- Defaults merge **at read time** only — registration never writes defaults into the database.
- Saves allowlist registered field IDs only.
- Removed/renamed fields: orphaned keys remain until reset/migration; they are not rewritten on save.
- Hidden conditional fields: previously saved values are preserved unless the submitted payload includes a new value (unchecked switches/checkboxes still coerce to `false` when the form is saved).

### Migrations

Future migrations can hook `mino_pre_save_options` / `mino_get_options` or run a one-time `update_option` routine keyed by `MINO_VERSION` in the consuming plugin.

## Saving and validation flow

1. Capability check for the page.
2. Nonce verification (`mino_save_settings_{page_id}`).
3. Strip framework meta keys from `$_POST`.
4. `Sanitizer::sanitize_page()` — type-aware, recursive for repeaters.
5. Drop non-persistent types (`heading`, `notice`, `html`).
6. `Validator::validate_page()` — required, email/url, custom callables.
7. `OptionStore::save()` → `mino_saved` action.

## Rendering flow

`PageRenderer` → `templates/page.php` → tab panels → `FieldRenderer` → field class `render()`.

Custom tabs: `callback` callable, or `template` + `template_base` resolved through `RequestGuard::resolve_template()` (realpath allowlist; no arbitrary includes).

## Conditional fields

- Declared via `require` or `dependency` arrays.
- JS toggles visibility client-side.
- Server save pipeline remains authoritative; visibility never expands the allowlist.

## Asset loading

Assets enqueue only on Mino Option admin pages. Color, media, editor, and sortable dependencies load only when field types request them.

## Extension points

See README “Extensibility” and `docs/security.md`. Primary hooks: `mino_booted`, `mino_register_page`, `mino_register_tab`, `mino_normalize_field`, `mino_page_fields`, `mino_page_defaults`, `mino_sanitize_field`, `mino_sanitize_page`, `mino_validate_page`, `mino_pre_save_options`, `mino_saved`, `mino_reset`, `mino_render_field`, `mino_field_attributes`, `mino_enqueue_assets`, `mino_field_type_registered`, `mino_user_can`, `mino_editor_settings`.

## Reference project notes

Inspected [optionino-framework](https://github.com/shokrino/optionino-framework) (MIT). Mino Option is an independent implementation. Notable Optionino limitations addressed here:

- Defaults written to DB on registration
- Boolean stored as `"true"`/`"false"` strings
- Highest-version loader race
- Tab `file` includes with weak path constraints
- Monolithic builder methods instead of field classes
- Broad asset loading patterns
