<script setup>
import EventBus from '@/objects/EventBus.js'
import events from '@/config/events.js'
import { useConfirm } from '@/stores/confirm'
import { storeToRefs } from 'pinia'
import { markRaw, watch } from 'vue'
import AppModal from '@/Components/Core/AppModal.vue'

const { show, title, message, okText, cancelText, component } =
  storeToRefs(useConfirm())

let rawComponent = null

watch(component, (value) => {
  if (!value) return
  rawComponent = markRaw(value)
})

function onClick(value) {
  if (value && component.value) {
    value = component.value.validate()

    if (!value) return
  }

  EventBus.emit(events.confirmed, value)
}
</script>
<template>
  <AppModal
    v-if="show"
    :model-value="show"
    :title="title"
    :persistent="false"
    :ok-text="okText"
    :cancel-text="cancelText"
    width="600px"
    @ok="onClick(true)"
    @close="onClick(false)"
  >
    {{ message }}

    <component
      :is="rawComponent"
      v-if="rawComponent"
      ref="component"
      class="my-3"
    />
  </AppModal>
</template>
