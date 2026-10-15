<script setup lang="ts">
import AppButton from '@/Components/Core/AppButton.vue'
import AppButtons from '@/Components/Core/AppButtons.vue'
import AppFiltersForm from '@/Components/Core/AppFiltersForm.vue'
import iSearch from '@/Components/Inputs/iSearch.vue'
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

const { searchInput, inputs, form, filterText, showAdvanced, setValue, reset } =
  useFilterForm(
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
  <div role="search" class="flex flex-col gap-3">
    <div class="flex items-center gap-2">
      <div class="w-full md:max-w-sm">
        <iSearch
          :model-value="form.search as string"
          :placeholder="Translator.actionTitle('search')"
          name="search"
          hide-label
          hide-details
          fluid
          @update:model-value="setValue(searchInput, $event)"
        />
      </div>

      <AppButton
        v-if="inputs.length"
        :title="Translator.tl('advanced')"
        :text="!showAdvanced"
        icon="filter_alt"
        size="small"
        severity="secondary"
        @click="showAdvanced = !showAdvanced"
      />

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

    <AppFiltersForm
      v-if="showAdvanced && inputs.length"
      :inputs="inputs"
      :values="form"
      @change="setValue"
    />
  </div>
</template>
