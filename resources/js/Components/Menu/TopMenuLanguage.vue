<script setup lang="ts">
import { computed } from 'vue'
import { MenuItem } from 'primevue/menuitem'
import AppButton from '@/Components/Core/AppButton.vue'
import useAuth from '@/composables/useAuth'
import { useConstants } from '@/composables/useConstants'
import { Translator } from '@/objects/Translator'

const { updateLanguage } = useAuth()
const { getConstants } = useConstants()

const items = computed<MenuItem[]>(() =>
  getConstants('languages').map(({ id, label }) => ({
    label,
    class: Translator.getLocale() === id ? 'font-semibold' : undefined,
    command: () => updateLanguage(id),
  }))
)
</script>
<template>
  <AppButton
    icon="translate"
    :title="Translator.tl('language')"
    :menu-items="items"
    text
    rounded
  />
</template>
