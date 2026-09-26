<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { MenuItem } from 'primevue/menuitem'
import AppButton from '@/Components/Core/AppButton.vue'
import useAuth from '@/composables/useAuth'
import { Translator } from '@/objects/Translator'

const { isLogged, user } = useAuth()

const loggedMenu = computed<MenuItem[]>(() => [
  {
    label: Translator.tl('profile'),
    command: () => router.visit(route('profile.edit')),
  },
  {
    label: Translator.tl('logout'),
    command: () => router.post(route('logout')),
  },
])
const unloggedMenu = computed<MenuItem[]>(() => [
  {
    label: Translator.actionTitle('login'),
    command: () => router.visit(route('login')),
  },
  {
    label: Translator.actionTitle('register'),
    command: () => router.visit(route('register')),
  },
])

const menuItems = computed<MenuItem[]>(() =>
  isLogged.value ? loggedMenu.value : unloggedMenu.value
)
</script>
<template>
  <AppButton
    :label="isLogged ? user?.name : Translator.actionTitle('enter')"
    :menu-items="menuItems"
    text
  />
</template>
