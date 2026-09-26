<script setup lang="ts">
import AppIcon from '@/Components/Core/AppIcon.vue'
import { Translator } from '@/objects/Translator'

defineProps<{ value?: boolean }>()
</script>
<template>
  <AppIcon v-if="value" icon="mdi-check" />
  <span v-else v-text="Translator.tl('no')" />
</template>
