<script lang="ts" setup>
import Image from 'primevue/image'

type Props = {
  src?: string
  alt?: string
  width?: string | number
  height?: string | number
}

defineProps<Props>()
</script>
<template>
  <Image
    :src="src"
    :alt="alt"
    :width="width"
    :height="height"
  />
</template>