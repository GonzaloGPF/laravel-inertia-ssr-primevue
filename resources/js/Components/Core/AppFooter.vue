<script lang="ts" setup>
import Menubar from 'primevue/menubar'
import { computed } from 'vue'

const items = computed(() => [
  {
    label: 'Boilerplate',
    icon: 'home',
  },
])
</script>
<template>
  <Menubar :model="items" :breakpoint="null" class="flex justify-center" />
</template>
