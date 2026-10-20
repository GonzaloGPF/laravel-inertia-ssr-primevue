<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import CardLayout from '@/Layouts/CardLayout.vue'

type Props = {
  title?: string
  /** Name of the route of the list of the entity, where the back button goes. */
  indexRoute: string
}

const props = defineProps<Props>()

const onBack = () => router.visit(route(props.indexRoute))
</script>
<template>
  <!-- The rest of what a CardLayout takes (i.e. `actions` and their events) goes straight to it -->
  <CardLayout :title="title" :prepend-actions="['back']" @back="onBack">
    <slot />
  </CardLayout>
</template>
