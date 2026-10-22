<script setup lang="ts" generic="T extends object">
import Button from 'primevue/button'
import Column from 'primevue/column'
import DataTable, {
  type DataTablePageEvent,
  type DataTableSortEvent,
} from 'primevue/datatable'
import ChevronDownIcon from '@primevue/icons/chevrondown'
import ChevronRightIcon from '@primevue/icons/chevronright'
import { omit } from 'lodash-es'
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
const COLLAPSE_MS = 300 // A bit more than the animation of the closing of a row
const LEAVE_MS = 300 // The animation of a row that leaves
const STAGGER_MS = 35 // Delay between the fade of one row and the next one
const STAGGER_ROWS = 14 // The rows from this one on fade in together
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
const expandedRows = ref<Record<string, boolean>>({})
const closingRows = ref<Record<string, boolean>>({}) // Rows that are closing, still expanded until it ends
const leavingRows = ref<Record<string, boolean>>({}) // Rows that were deleted, leaving until the items are loaded again

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

// The rows fade in when they appear (the loaded ones, a new page, the new results of a filter...),
// one after the other: the delay grows with the position of the row, up to a limit
const pt = {
  rowExpansionCell: { class: '!p-0' }, // The padding is in the content, so the row can start with no height
  bodyRow: ({ context }: { context: { index: number } }) =>
    isLeaving(context.index)
      ? {
          class:
            'animate-fadeoutright animate-duration-300 animate-fill-forwards motion-reduce:animate-none',
          style: { animationDelay: '0ms' },
        }
      : {
          class:
            'animate-fadein animate-duration-300 animate-fill-backwards motion-reduce:animate-none',
          style: {
            animationDelay: `${Math.min(context.index, STAGGER_ROWS) * STAGGER_MS}ms`,
          },
        },
}

const getRowId = (item: object) => String((item as Record<string, unknown>).id)

// An expanded row that is not closing
const isOpen = (item: T) =>
  !!expandedRows.value[getRowId(item)] && !closingRows.value[getRowId(item)]

const isLeaving = (index: number) => {
  const item = items.value[index]

  return !!item && !!leavingRows.value[getRowId(item)]
}

const isClosing = (item: T) => !!closingRows.value[getRowId(item)]

const prefersReducedMotion = () =>
  window.matchMedia('(prefers-reduced-motion: reduce)').matches

const collapse = (id: string) => {
  expandedRows.value = omit(expandedRows.value, id)
  closingRows.value = omit(closingRows.value, id)
}

// The row of PrimeVue is removed as soon as it is collapsed, so it is collapsed
// when its closing animation ends, or after a while if that is never notified
const closeRow = (id: string) => {
  if (prefersReducedMotion()) {
    return collapse(id)
  }

  closingRows.value = { ...closingRows.value, [id]: true }
  window.setTimeout(() => collapse(id), COLLAPSE_MS)
}

const toggleRow = (item: T) => {
  const id = getRowId(item)

  if (isClosing(item)) {
    return
  }

  if (expandedRows.value[id]) {
    return closeRow(id)
  }

  expandedRows.value = { ...expandedRows.value, [id]: true }
}

/**
 * Removes a row that has been deleted: it leaves with an animation, it is taken out of the
 * table right away, and the items are loaded again to know what takes its place.
 * It does not delete anything by itself.
 */
const removeRow = async (id: number | string) => {
  const key = String(id)

  if (!items.value.some((item) => getRowId(item) === key)) {
    return refresh()
  }

  if (expandedRows.value[key]) {
    closeRow(key)
  }

  leavingRows.value = { ...leavingRows.value, [key]: true }

  if (!prefersReducedMotion()) {
    await new Promise((resolve) => window.setTimeout(resolve, LEAVE_MS))
  }

  items.value = items.value.filter((item) => getRowId(item) !== key)
  total.value = Math.max(total.value - 1, 0)
  leavingRows.value = omit(leavingRows.value, key)

  await refresh()
}

const onDetailsAnimationEnd = (item: T) => {
  if (isClosing(item)) {
    collapse(getRowId(item))
  }
}

const onPage = (event: DataTablePageEvent) => goTo(event.page + 1, event.rows)

const onSort = (event: DataTableSortEvent) =>
  sortBy(event.sortField as string | undefined, event.sortOrder)

defineExpose({ refresh, removeRow })
</script>
<template>
  <DataTable
    :expanded-rows="expandedRows"
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
    :pt="pt"
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

    <Column v-if="details" class="w-12">
      <template #body="{ data }">
        <Button
          :aria-label="
            Translator.actionTitle(isOpen(data) ? 'collapse' : 'expand')
          "
          :aria-expanded="isOpen(data)"
          class="p-datatable-row-toggle-button"
          severity="secondary"
          text
          rounded
          @click="toggleRow(data)"
        >
          <template #icon>
            <ChevronDownIcon v-if="isOpen(data)" />
            <ChevronRightIcon v-else />
          </template>
        </Button>
      </template>
    </Column>

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
      <!-- The row opens as its content grows: a row can't animate its height, but this grid can -->
      <div
        :class="isClosing(data) ? 'animate-collapse' : 'animate-expand'"
        class="grid motion-reduce:animate-none"
        @animationend="onDetailsAnimationEnd(data)"
      >
        <div class="min-h-0 overflow-hidden">
          <div class="px-4 py-3">
            <component
              :is="details"
              v-bind="{ [detailsProp ?? 'item']: data }"
            />
          </div>
        </div>
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
