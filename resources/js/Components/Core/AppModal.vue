<script setup>
import { toRef } from 'vue'
import AppModalActions from '@/Components/Core/AppModalActions.vue'

const emits = defineEmits(['close', 'ok'])
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: null,
  },
  okText: {
    type: String,
    default: null,
  },
  cancelText: {
    type: String,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  closable: {
    type: Boolean,
    default: true,
  },
  dismissableMask: {
    type: Boolean,
    default: true,
  },
  modal: {
    type: Boolean,
    default: true,
  },
  maximizable: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  transition: {
    type: String,
    default: undefined,
  },
  hideActions: {
    type: Boolean,
    default: false,
  },
})
const show = toRef(props, 'modelValue')
const onClose = () => emits('close')
</script>
<template>
  <Dialog
    v-model:visible="show"
    :modal="modal"
    :header="title"
    :maximizable="maximizable"
    :closable="closable"
    :dismissableMask="dismissableMask"
  >
    <slot name="default" />

    <template #footer>
      <slot name="actions">
        <AppModalActions
          v-if="!hideActions"
          :cancel="cancelText"
          :ok="okText"
          class="flex space-x-2"
          @cancel="onClose"
          @ok="$emit('ok')"
        />
      </slot>
    </template>
  </Dialog>
</template>
