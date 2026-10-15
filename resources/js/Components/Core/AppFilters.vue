<script setup lang="ts">
import AppButtons from '@/Components/Core/AppButtons.vue'
import AppFiltersForm from '@/Components/Core/AppFiltersForm.vue'
import useFilterForm from '@/composables/useFilterForm'
import { Translator } from '@/objects/Translator'
import type { SearchValues } from '@/objects/QueryString'
import { ActionName } from '@/types/actions'
import type { FilterField } from '@/types/filter'

type Props = {
  filtersData: FilterField[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  /** The filters changed: the values written in the url query are given. */
  input: [values: SearchValues]
  refresh: []
}>()

const { inputs, form, filterText, setValue, reset } = useFilterForm(
  () => props.filtersData,
  (values) => emit('input', values)
)

const onAction = (action: ActionName) => {
  if (action === 'erase') {
    reset()
  }

  if (action === 'refresh') {
    emit('refresh')
  }
}
</script>
<template>
  <div v-if="inputs.length" role="search" class="flex flex-col gap-3">
    <div class="flex items-center justify-between gap-2">
      <h3 class="font-semibold" v-text="Translator.tl('filters')" />
      <AppButtons
        :actions="['refresh', 'erase']"
        class="gap-1"
        icon-only
        size="small"
        severity="secondary"
        text
        @click="onAction"
      />
    </div>

    <p
      v-if="filterText"
      class="text-xs"
      v-text="Translator.tl('filtering_by', { value: filterText })"
    />

    <AppFiltersForm :inputs="inputs" :values="form" @change="setValue" />
  </div>
</template>
