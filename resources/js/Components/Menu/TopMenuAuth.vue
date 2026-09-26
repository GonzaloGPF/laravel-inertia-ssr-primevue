<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { MenuItem } from 'primevue/menuitem'
import AppButton from '@/Components/Core/AppButton.vue'
import useAuth from '@/composables/useAuth'

const { isLogged, user } = useAuth()

const loggedMenu = computed<MenuItem[]>(() => [
  {
    label: user.value?.name,
    command: () => router.visit(route('profile.edit')),
  },
  {
    label: 'Logout',
    command: () => router.post(route('logout')),
  },
])
const unloggedMenu = computed<MenuItem[]>(() => [
  {
    label: 'Login',
    command: () => router.visit(route('login')),
  },
  {
    label: 'Register',
    command: () => router.visit(route('register')),
  },
])

const menuItems = computed<MenuItem[]>(() =>
  isLogged.value
    ? loggedMenu.value
    : unloggedMenu.value
)
</script>
<template>
  <AppButton
    :label="isLogged ? user?.name : 'Login'"
    :menu-items="menuItems"
    text
  />
</template>
