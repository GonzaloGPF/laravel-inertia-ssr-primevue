<script lang="ts" setup="">
import Image from 'primevue/image'

defineProps({
  src: {
    type: String,
    default: null,
  },
  alt: {
    type: String,
    default: null,
  },
  width: {
    type: [String, Number],
    default: null,
  },
  height: {
    type: [String, Number],
    default: null,
  },
})
</script>
<template>
  <Image :src="src" :alt="alt" :width="width" :height="height" />
</template>