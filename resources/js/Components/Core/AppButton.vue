<script setup lang="ts">
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import { useTemplateRef } from 'vue'
import { router } from '@inertiajs/vue3'
import AppIcon from '@/Components/Core/AppIcon.vue'
import AppModal from '@/Components/Core/AppModal.vue'
import { ButtonProps } from '@/types/buttons'

const emits = defineEmits(['mouseover', 'mouseenter', 'mouseleave', 'click'])
const props = defineProps<ButtonProps>()
const buttonElement = useTemplateRef('buttonElement')
const menuElement = useTemplateRef('menuElement')

function onClick(event: Event) {
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
      v-bind="{ ...$attrs, ...(title ? { title, 'aria-label': title } : {}) }"
      :label="label != null ? String(label) : undefined"
      :size="size"
      :severity="severity"
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
    >
      <template v-if="icon" #icon="{ class: iconClass }">
        <AppIcon :icon="icon" :class="iconClass" />
      </template>
    </Button>
    <AppModal
      v-if="modal"
      v-bind="modal.data || {}"
      v-on="modal.listeners || {}"
    >
      <slot name="modal" />
    </AppModal>
    <Menu
      v-if="!!menuItems"
      ref="menuElement"
      id="overlay_menu"
      :model="menuItems"
      :popup="!!menuItems"
    >
      <template #itemicon="{ item }">
        <AppIcon v-if="item.icon" :icon="item.icon" class="mr-2" />
      </template>
    </Menu>
  </div>
</template>
