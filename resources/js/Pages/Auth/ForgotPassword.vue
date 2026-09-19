<script setup lang="ts">
import { Translator } from '@/objects/Translator'
import ForgotPasswordForm from '@/Components/Forms/ForgotPasswordForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

type Props = {
  status: string
}

defineProps<Props>()
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
