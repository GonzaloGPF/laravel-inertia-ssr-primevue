<script setup>
import Translator from '@/objects/Translator.js'
import ResetPasswordForm from '@/Components/Forms/ResetPasswordForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

defineProps({
  email: {
    type: String,
    required: true,
  },
  token: {
    type: String,
    required: true,
  },
})
</script>
<template>
  <CardLayout :title="Translator.tl('reset_password')" class="max-w-7xl">
    <ResetPasswordForm :token="token" :email="email" />
  </CardLayout>
</template>
