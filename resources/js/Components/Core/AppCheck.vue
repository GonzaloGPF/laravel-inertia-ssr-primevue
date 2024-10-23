<script setup>
import AppIcon from '@/Components/Core/AppIcon.vue'
import Translator from '@/objects/Translator'

defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
})
</script>
<template>
  <AppIcon v-if="modelValue" icon="mdi-check" />
  <span v-else v-text="Translator.tl('no')" />
</template>
