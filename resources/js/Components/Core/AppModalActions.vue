<script setup lang="ts">
import Translator from '@/objects/Translator'
import AppButton from '@/Components/Core/AppButton.vue'
import { computed } from 'vue'

defineEmits(['cancel', 'ok'])
const props = defineProps({
  cancel: {
    type: String,
    default: null,
  },
  ok: {
    type: String,
    default: null,
  },
})

const cancelText = computed(() => {
  return props.cancel || Translator.actionTitle('cancel')
})
const okText = computed(() => {
  return props.ok || Translator.actionTitle('ok')
})
</script>
<template>
  <div>
    <AppButton
      :label="cancelText"
      :title="cancelText"
      size="small"
      variant="tonal"
      class="brand-font"
      @click="$emit('cancel')"
    />
    <AppButton
      :label="okText"
      :disabled="disabled || loading"
      :loading="loading"
      :title="okText"
      size="small"
      variant="tonal"
      class="brand-font"
      @click="$emit('ok')"
    />
  </div>
</template>
