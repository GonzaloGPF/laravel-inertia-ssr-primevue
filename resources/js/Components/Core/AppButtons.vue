<script setup lang="ts">
import ButtonGroup from 'primevue/buttongroup'
import AppButton from '@/Components/Core/AppButton.vue'
import { useButtons } from '@/composables/useButtons.js'
import { ButtonsProps } from '@/types/buttons'
import { computed } from 'vue'

defineEmits(['click'])
const props = defineProps<ButtonsProps>()

const buttons = computed(() => {
  if (props.buttons) {
    return props.buttons
  }
  return useButtons().getButtons(props.actions || [])
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
