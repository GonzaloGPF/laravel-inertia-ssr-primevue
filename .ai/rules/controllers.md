---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Listings use the Filter System, not custom endpoints
Every list of entities (admin tables, selectors...) must go through the Filter System: the model uses the `IsFilterable` trait, has an `App\Filters\{Model}Filter` extending `Filter`, and its `index` action returns `$this->renderCollection('Pages/{Model}Index', Model::class)`. That exposes the filtered and paginated `data` prop as `Inertia::optional`, which `AppDataTable` requests with a partial reload of the same Inertia route. Do not create per-entity `list`, `search` or `all` endpoints. Only in very exceptional cases, when a listing really cannot be expressed with the filters, create a specific endpoint, and say why. `api/autocomplete/{model}` already reuses the same filters.

## Sign users in and out with AuthService, not the Auth facade
Controllers and form requests never call `Auth::login`, `Auth::attempt` or `Auth::logout` (nor `auth()->login()`...): they use `App\Services\AuthService` (`login`, `attempt`, `logout`), the one place that signs users in and out on the web guard, so every way in (login form, registration, impersonation, OAuth, claim links...) behaves the same: the guard renews the session id and the CSRF token, and logging out invalidates the session. A new way to sign in is a call to the service, with the service growing a method when it needs one. Inject it in the action (`store(Request $request, AuthService $authService)`) or pass it to the form request (`$request->authenticate($authService)`).
