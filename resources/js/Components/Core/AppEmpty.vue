<script setup>
import { computed } from 'vue'
import Translator from '@/objects/Translator'

const props = defineProps({
  label: {
    type: String,
    default: null,
  },
  model: {
    type: String,
    default: null,
  },
})
const text = computed(() => {
  if (props.model) {
    return Translator.t('exceptions.without_data', {
      data: Translator.modelTitle(props.model, true),
    })
  }

  return props.label || Translator.tl('empty') + '...'
})
</script>
<template>
  <div v-text="text" />
</template>
