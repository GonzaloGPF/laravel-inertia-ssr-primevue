<script setup lang="ts">
import { Translator } from '@/objects/Translator'
import AppButton from '@/Components/Core/AppButton.vue'
import { computed } from 'vue'

type Props = {
  cancel?: string
  ok?: string
  disabled?: boolean
  loading?: boolean
}

defineEmits(['cancel', 'ok'])
const props = defineProps<Props>()

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
