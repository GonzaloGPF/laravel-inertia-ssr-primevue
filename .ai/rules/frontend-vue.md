---
paths:
  - 'resources/js/**'
---

# Frontend (Vue)

## Never use lodash-es `chain()` — it silently breaks in production builds
`chain(...)` from `lodash-es` relies on a dynamic method-mixin (`.filter()`, `.orderBy()`, `.transform()`, `.uniq()`, `.value()`, etc.) that Rollup's tree-shaking removes in a production build (`vite build`), even though it works fine under the Vite dev server. The failure is silent: no thrown error reaches the console (it happens inside a `computed` getter Vue swallows), the component just renders nothing. This broke `iSelect` in production (`TypeError: ...filter is not a function`) with zero visible symptoms in dev.

**How to apply**: never write `chain(x).method(...).value()`. Import the specific lodash-es functions you need (`filter`, `orderBy`, `uniq`, `map`, …) and call them as plain nested/sequential calls instead, e.g. `orderBy(filter(items, predicate), ['label'])`. See `resources/js/objects/Dropdown.ts`, `resources/js/objects/Filter.ts`, and `resources/js/composables/useFilterForm.ts` for the corrected pattern. If you find a new `chain(` anywhere, treat it as a bug and rewrite it the same way — verify any fix against an actual `vite build` (see `.ai/rules/browser.md`), not just the dev server, since this class of bug is invisible in dev.

## Call inject()-based composables synchronously in setup(), not lazily inside computed/watch
Composables that use Vue's `inject()` under the hood (e.g. `usePage()` from `@inertiajs/vue3`, and anything built on it like `useConstants()`) must be invoked directly in a component's `setup()` body. Calling them lazily inside a `computed(() => ...)` getter or a `watch` callback works most of the time (Vue keeps the instance context active during renders too), but it is fragile and not guaranteed — prefer resolving them once at the top of `setup()`/the composable function and referencing the resolved value inside the lazy callback.

**How to apply**: when writing a composable that wraps another composable, call the wrapped one immediately and destructure what you need, e.g. `const { getConstants } = useConstants()` at the top, then use `getConstants(...)` inside a `computed`. Do not write `useConstants().getConstants(...)` inline inside the computed getter.
