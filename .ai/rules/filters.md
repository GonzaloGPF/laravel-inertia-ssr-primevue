---
paths:
  - 'app/Filters/**'
---

# Filters

## Extend Filter to add listing capabilities instead of reimplementing them
`Filter` already handles, for any model: filtering by enum-cast columns (an array means whereIn), ranges `min_x`/`max_x`, exclusions `not_x`, `sort=attribute|asc|desc` (default order: `id desc`, then `updated_at desc`), `page`/`per_page`, `limit`, `ids`/`exceptIds`, `with`, `having`, `doesnt_have` and `count`. Add model specific filters as public methods named after the input (`email`, `verified`...). To enable the global `search` input of a table, declare `protected array $searchable = ['name', 'email']` in the model filter: every word typed must be found in any of those columns (LIKE, wildcards escaped). Do not reimplement filtering, sorting or pagination in controllers.
