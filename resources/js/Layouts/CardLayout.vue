<script setup lang="ts">
import AppAlert from '@/Components/Core/AppAlert.vue'
import AppToolbar from '@/Components/Core/AppToolbar.vue'
import Card from 'primevue/card'
import { Head } from '@inertiajs/vue3'
import { ActionName } from '@/types/actions'

type Props = {
  title?: string
  icon?: string
  description?: string
  alert?: string
  alertType?: string
  withoutHead?: boolean
  actions?: ActionName[]
  prependActions?: ActionName[]
  iconOnly?: boolean
}

defineEmits(['view', 'create', 'edit', 'delete', 'restore', 'download', 'back'])
withDefaults(defineProps<Props>(), { iconOnly: true })
</script>
<template>
  <Card>
    <template #title>
      <Head v-if="!withoutHead" :title="title" />
      <AppToolbar
        :title="title"
        :actions="actions"
        :prepend-actions="prependActions"
        :icon-only="iconOnly"
        @click="$emit($event)"
        class="p-2"
      />
    </template>
    <template #content>
      <div class="flex flex-col">
        <AppAlert
          v-if="!!alert"
          :message="alert"
          :type="alertType"
          class="mb-3"
        />
        <slot />
      </div>
    </template>
  </Card>
</template>
