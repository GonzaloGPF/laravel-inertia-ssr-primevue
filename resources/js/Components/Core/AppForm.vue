<script setup>
import Translator from '@/objects/Translator.js'
import AppButton from '@/Components/Core/AppButton.vue'
import { computed } from 'vue'

defineEmits(['submit', 'secondary'])
const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: null,
  },
  secondaryButton: {
    type: Object,
    default: null,
  },
  model: {
    type: Object,
    default: null,
  },
  hideActions: {
    type: Boolean,
    default: false,
  },
  horizontal: {
    type: Boolean,
    default: false,
  },
})
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
        :label="secondaryButton?.label"
        :href="secondaryButton?.href"
        :aspect="secondaryButton?.aspect"
        variant="text"
        size="small"
        @click="$emit('secondary', $event)"
      />
    </div>
  </form>
</template>
