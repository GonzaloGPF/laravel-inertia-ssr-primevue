<script setup>
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppModal from '@/Components/Core/AppModal.vue'
import design from '@/config/design.js'

const emits = defineEmits(['mouseover', 'mouseenter', 'mouseleave', 'click'])
const props = defineProps({
  icon: {
    type: String,
    default: null,
  },
  iconPosition: {
    type: String,
    default: null,
    validator: design.validPosition,
  },
  label: {
    type: [String, Number],
    default: null,
  },
  badge: {
    type: String,
    default: null,
  },
  badgeSeverity: {
    type: String,
    default: null,
    validator: design.validSeverity,
  },
  disabled: {
    type: Boolean,
    default: null,
  },
  loading: {
    type: Boolean,
    default: null,
  },
  loadingIcon: {
    type: String,
    default: null,
  },
  text: {
    type: Boolean,
    default: null,
  },
  rounded: {
    type: Boolean,
    default: null,
  },
  raised: {
    type: Boolean,
    default: false,
  },
  link: {
    type: Boolean,
    default: false,
  },
  outlined: {
    type: Boolean,
    default: false,
  },
  plain: {
    type: Boolean,
    default: false,
  },
  fluid: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: undefined,
    validator: design.validSize,
  },
  href: {
    type: String,
    default: null,
  },
  severity: {
    type: String,
    default: null,
    validator: design.validSeverity,
  },
  modal: {
    type: Object,
    default: null,
  },
  items: {
    type: Array,
    default: null,
  },
})
const buttonElement = ref()
const menuElement = ref()

function onClick(event) {
  if (props.href) {
    return router.visit(props.href)
  }
  if (menuElement.value) {
    return menuElement.value.toggle(event)
  }
  emits('click', event)
}

function click() {
  // buttonElement.value?.$el?.click()
}

defineExpose({
  click,
})
</script>
<template>
  <div>
    <Button
      ref="buttonElement"
      v-bind="$attrs"
      :label="label"
      :size="size"
      :severity="severity"
      :icon="icon"
      :icon-pos="iconPosition"
      :badge="badge"
      :badge-severity="badgeSeverity"
      :loading="loading"
      :loading-icon="loadingIcon"
      :raised="raised"
      :text="text"
      :link="link"
      :outlined="outlined"
      :plain="plain"
      :fluid="fluid"
      @click="onClick"
    />
    <AppModal
      v-if="modal"
      v-bind="modal.data || {}"
      v-on="modal.listeners || {}"
    >
      <slot name="modal" />
    </AppModal>
    <Menu
      v-if="!!items"
      ref="menuElement"
      id="overlay_menu"
      :model="items"
      :popup="!!items"
    />
  </div>
</template>
