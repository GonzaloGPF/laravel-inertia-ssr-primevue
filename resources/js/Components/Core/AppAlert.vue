<script setup lang="ts">
import design from '@/config/design.js'
import Message from 'primevue/message'

type Props = {
  severity?: string
  message?: string
  icon?: string
  blink?: boolean
}

defineProps<Props>()
</script>
<template>
  <Message :severity="severity" :icon="icon" :class="['alert', { blink }]">
    <slot>
      {{ message }}
    </slot>
  </Message>
</template>
<style>
.alert.blink {
  animation-name: blink;
  animation-duration: 20s;
  animation-timing-function: linear;
  animation-iteration-count: infinite;

  -webkit-animation-name: blink;
  -webkit-animation-duration: 2s;
  -webkit-animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
}

@keyframes blink {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
  100% {
    opacity: 1;
  }
}
</style>
