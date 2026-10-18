---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Listings use the Filter System, not custom endpoints
Every list of entities (admin tables, selectors...) must go through the Filter System: the model uses the `IsFilterable` trait, has an `App\Filters\{Model}Filter` extending `Filter`, and its `index` action returns `$this->renderCollection('Pages/{Model}Index', Model::class)`. That exposes the filtered and paginated `data` prop as `Inertia::optional`, which `AppDataTable` requests with a partial reload of the same Inertia route. Do not create per-entity `list`, `search` or `all` endpoints. Only in very exceptional cases, when a listing really cannot be expressed with the filters, create a specific endpoint, and say why. `api/autocomplete/{model}` already reuses the same filters.
