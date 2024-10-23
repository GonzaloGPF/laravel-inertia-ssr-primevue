<script setup>
import design from '@/config/design.js'
import Message from 'primevue/message'

defineProps({
  severity: {
    type: String,
    default: 'info',
    validator: design.validSeverity,
  },
  message: {
    type: String,
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  blink: {
    type: Boolean,
    default: false,
  },
})
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
