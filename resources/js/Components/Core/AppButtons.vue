<script setup>
import ButtonGroup from 'primevue/buttongroup'
import actionsData from '@/config/actions'
import { computed, ref, watch } from 'vue'
import Translator from '@/objects/Translator'
import AppButton from '@/Components/Core/AppButton.vue'
import Formatter from '@/objects/Formatter'

const emits = defineEmits(['click', 'update:model-value'])
const props = defineProps({
  modelValue: {
    type: Number,
    default: null,
  },
  actions: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})
const value = ref(props.modelValue)
const vButtons = computed(() =>
  (props.actions || [])
    .filter((action) => actionsData[action])
    .map((action) => ({
      ...actionsData[action],
      name: action,
      title: getTitle(actionsData[action].title),
    }))
)
const getTitle = (title) => {
  if (typeof title === 'function') {
    return title()
  }

  const path = `actions.${title}`

  if (Translator.te(path)) {
    return Translator.t(path)
  }

  return Formatter.ucFirst(title)
}

watch(value, (index) => {
  if (index === null || index === undefined) {
    return
  }

  emits('update:model-value', index)
}) // { immediate: true }
</script>
<template>
  <ButtonGroup>
    <AppButton
      v-for="(button, i) in vButtons"
      :key="i"
      :title="button.title"
      :disabled="disabled"
      :loading="loading"
      :icon="button.icon"
      :size="button.size"
      :text="button.text"
      :rounded="button.rounded"
      :raised="button.raised"
      :link="button.link"
      :outlined="button.outlined"
      :plain="button.plain"
      :fluid="button.fluid"
      @click="$emit('click', button.name)"
    />
  </ButtonGroup>
</template>
