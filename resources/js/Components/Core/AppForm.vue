<script setup lang="ts" generic="T extends { id: string | number }">
import { Translator } from '@/objects/Translator'
import AppButton from '@/Components/Core/AppButton.vue'
import { computed } from 'vue'
import { ButtonProps } from '@/types/buttons'

type Props = {
  loading?: boolean
  label?: string
  secondaryButton?: ButtonProps
  model?: Partial<T>
  hideActions?: boolean
  horizontal?: boolean
}

defineEmits(['submit', 'secondary'])

const props = defineProps<Props>()
const vLabel = computed(() => {
  if (props.label) {
    return props.label
  }

  return props.model?.id
    ? Translator.actionTitle('edit')
    : Translator.actionTitle('create')
})
</script>
<template>
  <form
    class="flex flex-col space-y-3"
    @keyup.enter.prevent
    @submit.prevent="$emit('submit', $event)"
  >
    <slot />
    <div
      v-if="!hideActions"
      :class="[
        'flex align-center justify-between',
        { 'flex-col space-y-3': !horizontal, 'space-x-3': horizontal },
      ]"
    >
      <AppButton
        :label="vLabel"
        :loading="loading"
        :disabled="loading"
        color="primary"
        type="submit"
      />
      <AppButton
        v-if="secondaryButton"
        v-bind="secondaryButton"
        :loading="loading"
        :disabled="loading"
        variant="text"
        size="small"
        @click="$emit('secondary', $event)"
      />
    </div>
  </form>
</template>
