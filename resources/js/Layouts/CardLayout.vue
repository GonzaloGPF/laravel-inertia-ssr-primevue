<script setup>
import AppAlert from '@/Components/Core/AppAlert.vue'
import AppToolbar from '@/Components/Core/AppToolbar.vue'
import Card from 'primevue/card'
import { Head } from '@inertiajs/vue3'

defineEmits(['view', 'create', 'edit', 'delete', 'restore', 'download', 'back'])
defineProps({
  title: {
    type: String,
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  description: {
    type: String,
    default: null,
  },
  alert: {
    type: String,
    default: null,
  },
  alertType: {
    type: String,
    default: 'info',
  },
  withoutHead: {
    type: Boolean,
    default: false,
  },
  buttons: {
    type: Array,
    default: null,
  },
  prependButtons: {
    type: Array,
    default: null,
  },
})
</script>
<template>
  <Card>
    <Head v-if="!withoutHead" :title="title" />
    <template #title>
      <AppToolbar
        :title="title"
        :buttons="buttons"
        :prepend-buttons="prependButtons"
        @click="$emit($event)"
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
