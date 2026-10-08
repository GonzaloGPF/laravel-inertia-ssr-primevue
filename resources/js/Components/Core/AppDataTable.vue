<script setup lang="ts" generic="T extends object">
import Column from 'primevue/column'
import DataTable, { type DataTablePageEvent } from 'primevue/datatable'
import { computed, ref, toRef } from 'vue'
import type { Component } from 'vue'
import AppButtons from '@/Components/Core/AppButtons.vue'
import AppEmpty from '@/Components/Core/AppEmpty.vue'
import useDataTable from '@/composables/useDataTable'
import { Translator } from '@/objects/Translator'
import { ActionName } from '@/types/actions'
import { TableActions, TableColumn } from '@/types/table'

type Props = {
  src: string
  columns: TableColumn<T>[]
  actions?: TableActions<T>
  headerActions?: ActionName[]
  title?: string
  description?: string
  detail?: Component
}

const ROWS_PER_PAGE_OPTIONS = [10, 25, 50, 100]

const props = defineProps<Props>()
const emit = defineEmits<{ action: [action: ActionName, item?: T] }>()
const { items, total, loading, currentPage, perPage, goTo, refresh } =
  useDataTable<T>(toRef(props, 'src'))
const expandedRows = ref({})

const first = computed(() => (currentPage.value - 1) * perPage.value)

const idColumn: TableColumn<T> = { field: 'id' }
const tableColumns = computed(() => [
  props.columns.find((column) => column.field === 'id') ?? idColumn,
  ...props.columns.filter((column) => column.field !== 'id'),
])

const hasActions = computed(
  () => !(Array.isArray(props.actions) && props.actions.length === 0)
)
const hasHeader = computed(
  () => !!(props.title || props.description || props.headerActions?.length)
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
    data-key="id"
    lazy
    paginator
    @page="onPage"
  >
    <template v-if="hasHeader" #header>
      <div class="flex items-center justify-between gap-4">
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
    </template>

    <Column v-if="detail" expander class="w-12" />

    <Column
      v-for="column in tableColumns"
      :key="column.field"
      :field="column.field"
      :header="getHeader(column)"
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

    <template v-if="detail" #expansion="{ data }">
      <component :is="detail" :item="data" />
    </template>
  </DataTable>
</template>
