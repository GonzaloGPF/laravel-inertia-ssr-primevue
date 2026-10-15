<script setup lang="ts">
import { omitBy } from 'lodash-es'
import AppForm from '@/Components/Core/AppForm.vue'
import { inputs as inputComponents } from '@/config/inputs'
import { Filter, type FilterInput } from '@/objects/Filter'
import type { FilterFormValues, FilterValue } from '@/types/filter'

type Props = {
  inputs: FilterInput[]
  values: FilterFormValues
}

const props = defineProps<Props>()
const emit = defineEmits<{ change: [input: FilterInput, value: FilterValue] }>()

// An autocomplete gives the selected item, but what is filtered is its attribute
const onChange = (input: FilterInput, value: FilterValue | object) => {
  if (value && typeof value === 'object' && !Array.isArray(value)) {
    const attribute = String(input.urlAttribute ?? 'name')

    value = (value as Record<string, string>)[attribute]
  }

  emit('change', input, value as FilterValue)
}

const getComponent = (input: FilterInput) =>
  inputComponents[input.input ?? 'text']

const getModelValue = (input: FilterInput) =>
  Filter.toModelValue(input, props.values)

// Only what the filter defines is given, the inputs don't share all their props
const getBindings = (input: FilterInput) =>
  omitBy(
    {
      name: input.name,
      label: typeof input.label === 'function' ? input.label() : input.label,
      options: input.options,
      src: input.src,
      model: input.model ?? (input.input === 'auto' ? input.src : undefined),
      urlAttribute: input.urlAttribute,
      type: input.type,
      multiple: input.multiple,
      min: input.min,
      max: input.max,
      range: input.range,
      filter: input.filter,
      binary: input.input === 'check' ? true : undefined,
      params: input.params,
    },
    (value) => value === undefined
  )
</script>
<template>
  <AppForm
    hide-actions
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 p-2"
  >
    <div
      v-for="input in inputs"
      :key="`${input.name}_${input.input ?? 'text'}`"
      class="w-full"
    >
      <component
        :is="getComponent(input)"
        v-bind="getBindings(input)"
        :model-value="getModelValue(input)"
        clearable
        fluid
        hide-details
        @update:model-value="onChange(input, $event)"
      />
    </div>
  </AppForm>
</template>
