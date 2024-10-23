<script setup>
import Markup from '@/objects/Markup.js'
import AppField from '@/Components/Core/AppField.vue'
import useAuth from '@/composables/useAuth.js'
import AppLogo from '@/Components/Core/AppLogo.vue'

const { user } = useAuth()
</script>
<template>
  <div>
    <AppField v-if="user" :model-value="Markup.role(user?.role)" type="html" />
    <div class="flex flex-col justify-center items-center mx-auto">
      <AppLogo class="mx-auto" />
      This is the root page
    </div>
  </div>
</template>
