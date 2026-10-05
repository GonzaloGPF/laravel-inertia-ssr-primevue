<script setup lang="ts">
import ButtonGroup from 'primevue/buttongroup'
import AppButton from '@/Components/Core/AppButton.vue'
import { useButtons } from '@/composables/useButtons.js'
import { ButtonsProps } from '@/types/buttons'
import { computed } from 'vue'

defineEmits(['click'])
const props = defineProps<ButtonsProps>()

const buttons = computed(() => {
  const list = props.buttons ?? useButtons().getButtons(props.actions || [])

  return list.map((button) => ({
    ...button,
    size: props.size ?? button.size,
    severity: props.severity ?? button.severity,
    text: props.text || button.text,
    ...(props.iconOnly
      ? { label: undefined, title: button.title ?? String(button.label ?? '') }
      : {}),
  }))
})
</script>
<template>
  <ButtonGroup>
    <AppButton
      v-for="(button, i) in buttons || []"
      :key="i"
      v-bind="button"
      @click="$emit('click', button.action)"
    />
  </ButtonGroup>
</template>
