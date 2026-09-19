<script setup lang="ts">
import AppModalActions from '@/Components/Core/AppModalActions.vue'
import { ModalProps } from '@/types/modal'

defineEmits(['close', 'ok'])
defineProps<ModalProps>()
const show = defineModel<boolean>()
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
          @cancel="$emit('close')"
          @ok="$emit('ok')"
        />
      </slot>
    </template>
  </Dialog>
</template>
