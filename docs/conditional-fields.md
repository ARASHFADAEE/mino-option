# Conditional fields

## Client vs server

- **JavaScript** shows/hides fields when dependencies change.
- **PHP save pipeline** does not use visibility to decide allowlisting. Only registered fields can be saved.
- Previously saved values for fields that become hidden remain in the option until overwritten or reset.

## Schema

```php
'require' => [
    'relation' => 'AND', // optional, default AND; also OR
    ['field_id', '==', true],
    ['other_id', 'in', ['a', 'b']],
],
```

`dependency` is accepted as an alias of `require`.

## Operators

| Op | Meaning |
|----|---------|
| `==` / `=` | Equality (booleans coerced) |
| `!=` | Inequality |
| `empty` | Empty string, empty array, null, false |
| `not_empty` | Opposite |
| `in` | Membership |
| `not_in` | Non-membership |

## Repeaters

Subfields may declare `require` rules. Evaluation scopes to the active panel; row-local dependency on sibling subfields is best-effort in JS (use absolute field names for page-level controls).
