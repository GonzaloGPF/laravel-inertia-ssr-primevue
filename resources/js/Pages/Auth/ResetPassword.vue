<script setup lang="ts">
import { Translator } from '@/objects/Translator'
import ResetPasswordForm from '@/Components/Forms/ResetPasswordForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

type Props = {
  email: string
  token: string
}

defineProps<Props>()
</script>
<template>
  <CardLayout :title="Translator.tl('reset_password')" class="max-w-7xl">
    <ResetPasswordForm :token="token" :email="email" />
  </CardLayout>
</template>
