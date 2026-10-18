<script setup lang="ts" generic="T extends object">
import Column from 'primevue/column'
import DataTable, {
  type DataTablePageEvent,
  type DataTableSortEvent,
} from 'primevue/datatable'
import { computed, ref, toRef } from 'vue'
import type { Component } from 'vue'
import AppButtons from '@/Components/Core/AppButtons.vue'
import AppEmpty from '@/Components/Core/AppEmpty.vue'
import AppFilters from '@/Components/Core/AppFilters.vue'
import useDataTable from '@/composables/useDataTable'
import { Translator } from '@/objects/Translator'
import { ActionName } from '@/types/actions'
import type { FilterField } from '@/types/filter'
import { TableActions, TableColumn } from '@/types/table'

type Props = {
  src: string
  columns: TableColumn<T>[]
  actions?: TableActions<T>
  headerActions?: ActionName[]
  title?: string
  description?: string
  /** Component rendered in the expanded row, which adds the column to expand it. */
  details?: Component
  /** Name of the prop of `details` that receives the item of the row. */
  detailsProp?: string
  filtersData?: FilterField[]
}

const ROWS_PER_PAGE_OPTIONS = [10, 25, 50, 100]
const PAGINATOR_TEMPLATE =
  'CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink RowsPerPageDropdown'

const props = defineProps<Props>()
const emit = defineEmits<{ action: [action: ActionName, item?: T] }>()
const {
  items,
  total,
  loading,
  currentPage,
  perPage,
  sortField,
  sortOrder,
  goTo,
  sortBy,
  refresh,
} = useDataTable<T>(toRef(props, 'src'))
const expandedRows = ref({})

const first = computed(() => (currentPage.value - 1) * perPage.value)

// PrimeVue fills its own tokens in the report, so they are given as the values
const pageReport = computed(() =>
  Translator.tl('showing_items', {
    first: '{first}',
    last: '{last}',
    total: '{totalRecords}',
  })
)

const idColumn: TableColumn<T> = { field: 'id' }
const tableColumns = computed(() => [
  props.columns.find((column) => column.field === 'id') ?? idColumn,
  ...props.columns.filter((column) => column.field !== 'id'),
])

const hasActions = computed(
  () => !(Array.isArray(props.actions) && props.actions.length === 0)
)
const hasHeader = computed(
  () =>
    !!(
      props.title ||
      props.description ||
      props.headerActions?.length ||
      props.filtersData?.length
    )
)

const getHeader = (column: TableColumn<T>) =>
  Translator.translate(column.header ?? column.field)

const getValue = (item: T, field: string) =>
  (item as Record<string, unknown>)[field]

const getCellProps = (column: TableColumn<T>, item: T) => {
  if (typeof column.props === 'function') {
    return column.props(item)
  }

  return column.props ?? { value: getValue(item, column.field) }
}

const getActions = (item: T): ActionName[] => {
  if (typeof props.actions === 'function') {
    return props.actions(item)
  }

  if (props.actions) {
    return props.actions
  }

  const isDeleted = !!getValue(item, 'deleted_at')

  return ['view', 'edit', isDeleted ? 'restore' : 'delete']
}

const onPage = (event: DataTablePageEvent) => goTo(event.page + 1, event.rows)

const onSort = (event: DataTableSortEvent) =>
  sortBy(event.sortField as string | undefined, event.sortOrder)

defineExpose({ refresh })
</script>
<template>
  <DataTable
    v-model:expanded-rows="expandedRows"
    :value="items"
    :loading="loading"
    :first="first"
    :rows="perPage"
    :total-records="total"
    :rows-per-page-options="ROWS_PER_PAGE_OPTIONS"
    :current-page-report-template="pageReport"
    :paginator-template="PAGINATOR_TEMPLATE"
    paginator-position="both"
    data-key="id"
    lazy
    paginator
    :sort-field="sortField"
    :sort-order="sortOrder"
    removable-sort
    @page="onPage"
    @sort="onSort"
  >
    <template v-if="hasHeader" #header>
      <div class="flex flex-col gap-4">
        <div
          v-if="title || description || headerActions?.length"
          class="flex items-center justify-between gap-4"
        >
          <div>
            <h2 v-if="title" class="text-xl font-semibold" v-text="title" />
            <p v-if="description" class="text-sm" v-text="description" />
          </div>
          <AppButtons
            v-if="headerActions?.length"
            :actions="headerActions"
            @click="emit('action', $event)"
          />
        </div>

        <!-- The table requests the items again when the url query changes, so there is no need to listen to the filters `input` -->
        <AppFilters
          v-if="filtersData?.length"
          :filters-data="filtersData"
          @refresh="refresh"
        />
      </div>
    </template>

    <Column v-if="details" expander class="w-12" />

    <Column
      v-for="column in tableColumns"
      :key="column.field"
      :field="column.field"
      :header="getHeader(column)"
      :sortable="!column.without_sort"
    >
      <template #body="{ data }">
        <component
          :is="column.component"
          v-if="column.component"
          v-bind="getCellProps(column, data)"
        />
        <template v-else>{{ getValue(data, column.field) }}</template>
      </template>
    </Column>

    <Column v-if="hasActions" :header="Translator.tl('actions')">
      <template #body="{ data }">
        <AppButtons
          :actions="getActions(data)"
          class="gap-1"
          icon-only
          size="small"
          severity="secondary"
          text
          @click="emit('action', $event, data)"
        />
      </template>
    </Column>

    <template #empty>
      <AppEmpty />
    </template>

    <template v-if="details" #expansion="{ data }">
      <div class="px-4 py-3">
        <component :is="details" v-bind="{ [detailsProp ?? 'item']: data }" />
      </div>
    </template>
  </DataTable>
</template>
<style>
.p-paginator-content {
  width: 100%;
  padding-right: 0.25rem;
  padding-left: 0.25rem;
}
.p-paginator-current {
  margin-right: auto;
}
</style>
