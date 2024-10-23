<script setup lang="ts">
import useAuth from '@/composables/useAuth'
import AppNavLink from '@/Components/Core/AppNavLink.vue'

const { isLogged } = useAuth()
</script>
<template>
  <div>
    <AppNavLink v-if="!isLogged" :href="$route('login')" :label="'Login'" />
    <AppNavLink
      v-else
      :href="$route('logout')"
      :label="'Logout'"
      method="post"
    />
  </div>
</template>