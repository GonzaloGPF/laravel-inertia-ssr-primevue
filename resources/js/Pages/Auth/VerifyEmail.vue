<script setup lang="ts">
import { Translator } from '@/objects/Translator'
import VerifyEmailForm from '@/Components/Forms/VerifyEmailForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

type Props = {
  status: string
}

defineProps<Props>()
</script>
<template>
  <CardLayout
    :title="Translator.tl('verify')"
    :description="Translator.t('help.verify_email')"
    :alert="
      status === 'verification-link-sent'
        ? Translator.t('help.verify_email_sent')
        : undefined
    "
    class="max-w-7xl"
  >
    <VerifyEmailForm />
  </CardLayout>
</template>
