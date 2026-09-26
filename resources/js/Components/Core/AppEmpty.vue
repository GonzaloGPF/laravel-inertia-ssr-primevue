<script setup lang="ts">
import { computed } from 'vue'
import { Translator } from '@/objects/Translator'

type Props = {
  label?: string
  model?: string
}

const props = defineProps<Props>()
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
