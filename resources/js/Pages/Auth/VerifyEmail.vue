<script setup>
import Translator from '@/objects/Translator.js'
import VerifyEmailForm from '@/Components/Forms/VerifyEmailForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

defineProps({
  status: {
    type: String,
  },
})
</script>
<template>
  <CardLayout
    :title="Translator.tl('verify')"
    :description="Translator.t('help.verify_email')"
    :alert="
      status === 'verification-link-sent'
        ? Translator.t('help.verify_email_sent')
        : null
    "
    class="max-w-7xl"
  >
    <VerifyEmailForm />
  </CardLayout>
</template>
