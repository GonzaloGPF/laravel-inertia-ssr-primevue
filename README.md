# Laravel + Inertia SSR + PrimeVue boilerplate

A starting point for admin-style web apps: Laravel on the back, a Vue 3 + TypeScript SPA with server side rendering on the front, [PrimeVue](https://primevue.org) components, and a reusable **Filter System** that powers every listing. It runs in Docker with [Laravel Sail](https://laravel.com/docs/sail).

It comes with authentication (Breeze based), a profile page, English and Spanish translations and a complete users administration (list, create, edit and delete) that works as the reference for any new admin section.

## Stack

| | |
| --- | --- |
| Backend | PHP 8.4, Laravel 13, Sanctum, [EloquentFilter](https://github.com/Tucker-Eric/EloquentFilter), [Ziggy](https://github.com/tighten/ziggy) |
| Frontend | Inertia v3 (SSR), Vue 3, TypeScript, Pinia, [PrimeVue 4](https://primevue.org) (Aura theme), Tailwind CSS 4, Vite 8 |
| i18n | `laravel-vue-i18n`: the Laravel `lang/` files (`es`, `en`) are shared with the frontend |
| Tests | Pest 5, with browser tests (Playwright) |
| Tooling | Laravel Pint, ESLint, Prettier, Stylelint, vue-tsc, Husky + lint-staged, GitHub Actions |
| AI | Laravel Boost (guidelines, skills and MCP) and project rules in `.ai/rules` |

## Getting started

Docker is the only requirement.

```bash
# 1. Environment. .env.example defaults to SQLite: with Sail, use the MySQL service
cp .env.example .env
#    DB_CONNECTION=mysql  DB_HOST=mysql  DB_PORT=3306
#    DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...

# 2. PHP dependencies, without PHP installed on your machine
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php84-composer:latest composer install --ignore-platform-reqs

# 3. Services (app, MySQL, Redis, Mailpit and Meilisearch, which the app doesn't use yet)
vendor/bin/sail up -d

# 4. Application key, database and demo data
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate --seed

# 5. Frontend dependencies and dev server
vendor/bin/sail yarn install
vendor/bin/sail yarn dev
```

Every command runs through Sail, as the app lives in the containers. The seeders create an admin (`admin@mail.com`), a regular user (`test@mail.com`) and 30 random ones, all with the password `Secret123**`.

> `migrate --seed` truncates every table before seeding. Don't run it against data you want to keep.

### Dev server or build?

Laravel serves the assets from the Vite dev server when `public/hot` exists, and from `public/build` when it doesn't. The build is a snapshot: it doesn't know about your edits until you run `vendor/bin/sail yarn build` again. If you don't see a change in the browser, check that `yarn dev` is running, and that you are not looking at an old build.

## Commands

| | |
| --- | --- |
| `vendor/bin/sail artisan test --compact` | Whole suite: unit, feature and browser tests |
| `vendor/bin/sail bin pint` | PHP code style |
| `vendor/bin/sail yarn eslint resources/js` | JS / TS / Vue lint |
| `vendor/bin/sail yarn prettier --check resources/js` | Frontend formatting |
| `vendor/bin/sail yarn stylelint 'resources/css/**/*'` | CSS lint |
| `vendor/bin/sail yarn vue-tsc --noEmit` | Type check |
| `vendor/bin/sail yarn build` | Type check, and client and SSR production builds |

The browser tests drive a headless Chromium from PHP and run inside Sail, which already has what Chromium needs. Run them against the production build too (move `public/hot` away and run `yarn build`) when you change frontend code: some failures only show there.

A Husky pre-commit hook runs lint-staged (Pint, ESLint and Stylelint on the staged files). GitHub Actions run the same checks, the tests with MySQL, and a production build smoke check on every push to `master` and every pull request.

## Project structure

```
app/
  Filters/             Model filters (EloquentFilter): Filter.php is the base one
  Http/Controllers/    Thin controllers, they render Inertia pages
  Policies/            Authorization rules
  Services/            FileService: saves and deletes the files of the models
  Traits/IsFilterable  Filtering and pagination for the models
  Traits/HasFiles      Files (avatars...) for the models
lang/{en,es}/          Translations, shared with the frontend
resources/js/
  Pages/               Inertia pages, one folder per section
  Layouts/             Page layouts: BaseLayout, CardLayout and ShowLayout for the show pages
  Components/
    Core/              App* components: AppDataTable, AppFilters, AppModal, AppButtons...
    Inputs/            Form inputs (iText, iSelect, iSearch...), the only ones to use in forms
    Forms/             Forms of the pages, they use AppForm and the inputs
    Info/              Info components: they just display the values of an entity (UserInfo)
  composables/         useCrud, useDataTable, useFilterForm, useForm...
  config/              columns.ts, inputs.ts, buttons.ts... and filters/ (one file per entity)
.ai/rules/             Project rules for AI assistants, grouped by area
tests/{Unit,Feature,Browser}
```

## The Filter System

Every listing of entities, such as an admin table or a selector, goes through the **Filter System**. It is more than enough for listing entities: a specific endpoint for a listing is only created in very exceptional cases, when the filters cannot express it.

How a listing is built:

1. **Model.** Use the `IsFilterable` trait.
2. **Filter.** Create `App\Filters\{Model}Filter`, extending `Filter`. The base class already handles ranges (`min_x`, `max_x`), exclusions (`not_x`), enum columns, `sort=attribute|asc`, pagination (`page`, `per_page`), `ids`, `with` and more. Add model specific filters as methods named after the input, and declare the columns of the global search:

   ```php
   class UserFilter extends Filter
   {
       protected array $searchable = ['name', 'email', 'phone'];

       public function verified($value): Builder { /* ... */ }
   }
   ```

3. **Controller.** `index` returns `$this->renderCollection('Users/UserIndex', User::class)`. The filtered and paginated `data` prop is only resolved when the table asks for it with a partial reload.
4. **Page.** Render an `AppDataTable` with its columns and filters:

   ```vue
   <AppDataTable
     ref="table"
     :src="route('users.index')"
     :columns="userColumns"
     :filters-data="userFilters"
     :header-actions="['create']"
     @action="onAction"
   />
   ```

   `useCrud('user')` gives the create / edit / delete flow (modal, confirmation and refresh) that the page connects to the table, and the `view` action, which opens the show page of the row (`Pages/Users/UserShow.vue`). That page keeps the user the backend gives in the `currentUser` of `stores/userStore.ts` and shows it with `UserInfo`, inside a `ShowLayout`: the layout every show page shares, a `CardLayout` with a back button to the list (`index-route`) and the content in its slot. See `resources/js/Pages/Users/UserIndex.vue`.

`AppFilters` shows a global search input (it looks into the `searchable` columns, every word has to match) and an *Advanced* toggle with one input per field of the entity's filters file (`config/filters/userFilters.ts`, one file per entity). Each field says which input to render (`text`, `select`, `auto`, `boolean`, `check`, `number`, `number_range`, `date`), plus its options or visibility. A range is a field with `range: true` (`{ name: 'age', input: 'number_range', range: true }`, or `input: 'date'`), sent as `min_age` and `max_age`. The filters, the sort, the page and the page size live in the url (`?search=ada&role[]=admin&sort=name|asc&page=2&perPage=25`): they can be shared, and the browser back button works.

A table can also expand its rows with `:details="UserInfo"` (an Info component, `details-prop` names the prop that receives the row, `item` by default): the column to expand them only exists when `details` is given.

The columns are sortable by the backend: click a header to sort ascending, again for descending, and a third time to remove the sort. A column that must not be sortable declares `without_sort: true` in `config/columns.ts`.

## Files

Any model can own files with the `HasFiles` trait (and `HasFilesInterface`). The files are rows of the polymorphic `files` table that hold only the `path` and the `disk` where they were saved: the `url` is calculated, and the physical files live in the disk of `config('filesystems.files_disk')` (`FILES_DISK`: `public` locally, `s3` in production, and a temporary folder in the tests). With the `public` disk, run `vendor/bin/sail artisan storage:link` once.

The model declares its collections of files, and the `FileService` (which the trait reaches) saves them, replaces them and deletes them, physical files included:

```php
class User extends Authenticatable implements HasFilesInterface
{
    use HasFiles;

    public function fileCollections(): array
    {
        return ['avatar' => ['single' => true, 'mimes' => ['jpg', 'jpeg', 'png', 'webp'], 'max' => 2048]];
    }

    public function avatar(): MorphOne
    {
        return $this->fileOf('avatar');
    }
}

$user->saveFile($request->file('avatar'), 'avatar'); // saves it, or replaces the previous one of a single file collection
$user->deleteFile('avatar');
$request->validate(['avatar' => User::fileRules('avatar')]); // the rules come from the collection
```

The files of a model are deleted with it (a soft deleted model keeps them until it is force deleted). `$user->syncFile('avatar', $upload, $remove)` is what the forms use (saves the upload, or deletes the file when asked). The user's avatar is chosen in the users form and in the profile with the `iImage` input and shown with `AppAvatar`, which falls back to the initials of the name. As a file cannot go in a PUT request, a form that uploads a file to an update sends a POST with `_method: 'put'`.

## Ids

The models have a **ulid** as their primary key, named `id` as always: it can be exposed in the urls, the props and the filters, but nobody can count or walk through the records, as it happens with the sequential numbers. It is sortable by creation date, so the lists keep showing the newest first.

- A table declares it with `$table->ulid('id')->primary()` and the foreign keys to it with `foreignUlid()` (`ulidMorphs()` for the polymorphic ones). The model uses `HasUlids`.
- `route('users.show', $user)` and the route bindings use it (`/admin/users/01kdrxgzd049frv51hjbnbjv58`). Anything that is not a ulid is a 404.
- The files of a model are saved in a folder named after its id (`users/{id}/avatar`).

This is not an access control: every endpoint still goes through its policy.

## Conventions

- Forms use the components of `Components/Inputs` and `AppForm`.
- Texts are translated: add them to `lang/en` and `lang/es`.
- Use named routes (`route('users.index')`).
- Add tests with every change, and verify the frontend with browser tests.

## AI assistants

The project is prepared for AI coding assistants with [Laravel Boost](https://laravel.com/docs/boost):

- `CLAUDE.md` and `AGENTS.md` hold the Boost guidelines. They are generated: `composer dump-autoload` refreshes them.
- `.ai/rules/index.md` maps file globs to the project rules (the Filter System, the Vue pitfalls, how to verify the UI with browser tests...). Assistants read it before planning or editing. Rules are recorded with Boost's `record-rule` tool, and they are committed.
- `.mcp.json` starts the Boost MCP server through Sail.

## License

The project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
