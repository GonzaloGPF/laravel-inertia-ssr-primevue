<script setup>
import AppButtons from '@/Components/Core/AppButtons.vue'

defineEmits(['click'])
defineProps({
  title: {
    type: String,
    default: null,
  },
  color: {
    type: String,
    default: null,
  },
  elevation: {
    type: String,
    default: null,
  },
  buttons: {
    type: Array,
    default: null,
  },
  prependButtons: {
    type: Array,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})
</script>
<template>
  <div density="compact" :color="color" :elevation="elevation" flat>
    <AppButtons
      :buttons="prependButtons"
      :loading="loading"
      :disabled="disabled"
      @click="$emit('click', $event)"
    />
    <div>
      {{ title }}
    </div>
    <slot />
    <AppButtons
      :buttons="buttons"
      :loading="loading"
      :disabled="disabled"
      @click="$emit('click', $event)"
    />
  </div>
</template>
