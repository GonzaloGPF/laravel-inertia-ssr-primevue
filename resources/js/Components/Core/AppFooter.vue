<script lang="ts" setup>
import Menubar from 'primevue/menubar'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const items = computed(() => [
  {
    label: page.props.appName,
    icon: 'home',
  },
])
</script>
<template>
  <Menubar :model="items" class="flex justify-center" />
</template>
