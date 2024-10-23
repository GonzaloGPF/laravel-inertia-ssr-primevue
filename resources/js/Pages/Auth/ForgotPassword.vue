<script setup>
import Translator from '@/objects/Translator.js'
import ForgotPasswordForm from '@/Components/Forms/ForgotPasswordForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

defineProps({
  status: {
    type: String,
  },
})
</script>
<template>
  <CardLayout
    :title="Translator.tl('forgot_password')"
    :description="Translator.t('help.forgot_password')"
    class="max-w-7xl"
  >
    <ForgotPasswordForm />
  </CardLayout>
</template>
