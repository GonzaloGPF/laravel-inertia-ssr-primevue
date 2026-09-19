<script setup lang="ts">
import AppAlert from '@/Components/Core/AppAlert.vue'
import AppToolbar from '@/Components/Core/AppToolbar.vue'
import Card from 'primevue/card'
import { Head } from '@inertiajs/vue3'
import { ButtonProps } from '@/types/buttons'

type Props = {
  title?: string
  icon?: string
  description?: string
  alert?: string
  alertType?: string
  withoutHead?: boolean
  buttons?: ButtonProps[]
  prependButtons?: ButtonProps[]
}

defineEmits(['view', 'create', 'edit', 'delete', 'restore', 'download', 'back'])
defineProps<Props>()
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
