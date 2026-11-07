<script setup lang="ts">
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { MenuItem } from 'primevue/menuitem'
import AppButton from '@/Components/Core/AppButton.vue'
import { Translator } from '@/objects/Translator'
import { useTheme } from '@/stores/theme'
import type { ThemeMode } from '@/types/theme'

const ICONS: Record<ThemeMode, string> = {
  light: 'light_mode',
  dark: 'dark_mode',
  system: 'contrast',
}

const theme = useTheme()
const { mode } = storeToRefs(theme)

const items = computed<MenuItem[]>(() =>
  (Object.keys(ICONS) as ThemeMode[]).map((value) => ({
    label: Translator.tl(value),
    icon: ICONS[value],
    class: mode.value === value ? 'font-semibold' : undefined,
    command: () => theme.setMode(value),
  }))
)
</script>
<template>
  <AppButton
    :icon="ICONS[mode]"
    :title="Translator.tl('theme')"
    :menu-items="items"
    text
    rounded
  />
</template>
