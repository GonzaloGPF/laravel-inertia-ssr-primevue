---
paths:
  - 'resources/js/Pages/**'
---

# Pages

## List views are an AppDataTable with filters-data
A page that lists entities renders `AppDataTable` with `:src="route('{resource}.index')"`, `:columns` (config/columns.ts), `:filters-data` (config/filters.ts) and `useCrud('{model}')` for the create/edit/delete modal flow, as `Pages/Users/UserIndex.vue` does. The filters (global `search`, the advanced inputs, `page`, `perPage`) live in the url query and the backend does the filtering, sorting and pagination: never keep list or filter state in components, nor fetch lists by hand. To add a filter, add a field to config/filters.ts: `name` is the backend filter input and `input` one of text, select, auto, boolean, check, number, number_range or date (inputs come from components/Inputs, registered in config/inputs.ts). Cells that are not plain text use `column.component`.
