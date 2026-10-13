# Architecture

## Overview

Arash Settings Framework (ASF) is a namespaced, modular WordPress settings toolkit designed to be bundled inside plugins and themes.

## Component responsibilities

| Component | Responsibility |
|-----------|----------------|
| `arash-settings-framework.php` | Multi-instance loader, constants, boot |
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

1. Bundle metadata is pushed into `$GLOBALS['asf_loader_registry']`.
2. **First loaded copy wins.** Later copies are skipped; major mismatches are recorded as conflicts.
3. Autoloader + `functions.php` load; `Framework::boot()` registers hooks once.
4. Consumer plugins call `Settings::register_page()` / `register_tab()` on `plugins_loaded` (or earlier).
5. On `admin_menu`, menus are created from the registry.
6. On `admin_enqueue_scripts`, assets load only when `$_GET['page']` matches a registered slug.
7. On `admin_init`, `SaveHandler` processes authorized POST saves/resets.

## Multi-instance strategy

**Decision:** first-loaded version locks the runtime.

Why not “highest version wins”?

- Optionino’s approach can swap implementations after another plugin has already registered settings against an older API shape.
- ASF refuses to replace a booted runtime. Incompatible majors surface under `WP_DEBUG`.

Trade-off: an older copy loaded first keeps running. Document that host plugins should load ASF early, or ship a single shared mu-plugin copy when possible.

## Persistence

- One WordPress option array per page (`option_key`, defaulting to page `id`).
- Defaults merge **at read time** only — registration never writes defaults into the database.
- Saves allowlist registered field IDs only.
- Removed/renamed fields: orphaned keys remain until reset/migration; they are not rewritten on save.
- Hidden conditional fields: previously saved values are preserved unless the submitted payload includes a new value (unchecked switches/checkboxes still coerce to `false` when the form is saved).

### Migrations

Future migrations can hook `asf_pre_save_options` / `asf_get_options` or run a one-time `update_option` routine keyed by `ASF_VERSION` in the consuming plugin.

## Saving and validation flow

1. Capability check for the page.
2. Nonce verification (`asf_save_settings_{page_id}`).
3. Strip framework meta keys from `$_POST`.
4. `Sanitizer::sanitize_page()` — type-aware, recursive for repeaters.
5. Drop non-persistent types (`heading`, `notice`, `html`).
6. `Validator::validate_page()` — required, email/url, custom callables.
7. `OptionStore::save()` → `asf_saved` action.

## Rendering flow

`PageRenderer` → `templates/page.php` → tab panels → `FieldRenderer` → field class `render()`.

Custom tabs: `callback` callable, or `template` + `template_base` resolved through `RequestGuard::resolve_template()` (realpath allowlist; no arbitrary includes).

## Conditional fields

- Declared via `require` or `dependency` arrays.
- JS toggles visibility client-side.
- Server save pipeline remains authoritative; visibility never expands the allowlist.

## Asset loading

Assets enqueue only on ASF admin pages. Color, media, editor, and sortable dependencies load only when field types request them.

## Extension points

See README “Extensibility” and `docs/security.md`. Primary hooks: `asf_booted`, `asf_register_page`, `asf_register_tab`, `asf_normalize_field`, `asf_page_fields`, `asf_page_defaults`, `asf_sanitize_field`, `asf_sanitize_page`, `asf_validate_page`, `asf_pre_save_options`, `asf_saved`, `asf_reset`, `asf_render_field`, `asf_field_attributes`, `asf_enqueue_assets`, `asf_field_type_registered`, `asf_user_can`, `asf_editor_settings`.

## Reference project notes

Inspected [optionino-framework](https://github.com/shokrino/optionino-framework) (MIT). ASF is an independent implementation. Notable Optionino limitations addressed here:

- Defaults written to DB on registration
- Boolean stored as `"true"`/`"false"` strings
- Highest-version loader race
- Tab `file` includes with weak path constraints
- Monolithic builder methods instead of field classes
- Broad asset loading patterns
