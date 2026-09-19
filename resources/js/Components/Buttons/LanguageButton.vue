<script setup lang="ts">
import AppDropdown from '@/Components/Core/AppDropdown.vue'
import AppButton from '@/Components/Core/AppButton.vue'
import { computed } from 'vue'
import { Translator } from '@/objects/Translator'
import AppImage from '@/Components/Core/AppImage.vue'

const flags = computed(() => [
  {
    title: Translator.tl('spanish'),
    src: '/images/flags/es.svg',
    locale: 'es',
  },
  {
    title: Translator.tl('english'),
    src: '/images/flags/gr.svg',
    locale: 'en',
  },
])

const filteredFlags = computed(() =>
  flags.value.filter((flag) => flag.locale !== Translator.getLocale())
)
const currentFlag = computed(() =>
  flags.value.find((flag) => flag.locale === Translator.getLocale())
)
</script>
<template>
  <AppDropdown align="right" width="25">
    <template #trigger>
      <AppButton variant="plain">
        <AppImage
          :src="currentFlag?.src"
          :alt="currentFlag?.title"
          width="25"
        />
      </AppButton>
    </template>

    <template #content>
      <AppButton
        v-for="flag in filteredFlags"
        :key="flag.locale"
        variant="plain"
        class="w-full"
        @click="Translator.setLocale(flag.locale)"
      >
        <AppImage
          :src="flag.src"
          :alt="flag.title"
          width="25"
        />
      </AppButton>
    </template>
  </AppDropdown>
</template>
