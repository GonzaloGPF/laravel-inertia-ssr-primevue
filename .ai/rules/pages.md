---
paths:
  - 'resources/js/Pages/**'
---

# Pages

## List views are an AppDataTable with filters-data
A page that lists entities renders `AppDataTable` with `:src="route('{resource}.index')"`, `:columns` (config/columns.ts), `:filters-data` (config/filters/{entity}Filters.ts, one file per entity) and `useCrud('{model}')` for the create/edit/delete modal flow, as `Pages/Users/UserIndex.vue` does. The filters (global `search`, the advanced inputs, `page`, `perPage`) live in the url query and the backend does the filtering, sorting and pagination: never keep list or filter state in components, nor fetch lists by hand. To add a filter, add a field to the filters file of the entity: `name` is the backend filter input and `input` one of text, search, select, auto, boolean, check, number, number_range or date. A range (`min_x`/`max_x` in the backend) is a field with `range: true` and the input `date` or `number_range` (inputs come from components/Inputs, registered in config/inputs.ts). Cells that are not plain text use `column.component`. Every column with a field is sortable by default, by the backend (`?sort=field|asc|desc` in the url, three clicks: ascending, descending, none); a column that must not be sortable declares `without_sort: true` in its definition (config/columns.ts).
