<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { MenuItem } from 'primevue/menuitem'
import AppAvatar from '@/Components/Core/AppAvatar.vue'
import AppButton from '@/Components/Core/AppButton.vue'
import useAuth from '@/composables/useAuth'
import { Translator } from '@/objects/Translator'

const { isLogged, isAdmin, user } = useAuth()

const personalItems = computed<MenuItem[]>(() => [
  {
    label: Translator.tl('profile'),
    icon: 'person',
    command: () => router.visit(route('profile.edit')),
  },
  {
    label: Translator.tl('logout'),
    icon: 'logout',
    command: () => router.post(route('logout')),
  },
])

const adminItems = computed<MenuItem[]>(() => [
  {
    label: Translator.tl('dashboard'),
    icon: 'dashboard',
    command: () => router.visit(route('dashboard.show')),
  },
  {
    label: Translator.modelTitle('user', true),
    icon: 'group',
    command: () => router.visit(route('users.index')),
  },
])

const loggedMenu = computed<MenuItem[]>(() => {
  if (!isAdmin.value) {
    return personalItems.value
  }

  return [
    { label: Translator.tl('admin'), items: adminItems.value },
    { separator: true },
    { label: Translator.tl('personal_area'), items: personalItems.value },
  ]
})

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
  <div class="flex items-center gap-1">
    <AppAvatar v-if="isLogged" :src="user?.avatar_url" :name="user?.name" />
    <AppButton
      :label="isLogged ? user?.name : Translator.actionTitle('enter')"
      :menu-items="menuItems"
      text
    />
  </div>
</template>
