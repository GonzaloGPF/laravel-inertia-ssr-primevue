<script setup lang="ts">
import AppLogo from '@/Components/Core/AppLogo.vue'
import LoginForm from '@/Components/Forms/LoginForm.vue'
import { Translator } from '@/objects/Translator'
import CardLayout from '@/Layouts/CardLayout.vue'

type Props = {
  status?: string
}

defineProps<Props>()
</script>
<template>
  <CardLayout
    :title="Translator.actionTitle('login')"
    :alert="status || undefined"
  >
    <div class="flex flex-col justify-center">
      <AppLogo class="my-5" />
      <LoginForm />
    </div>
  </CardLayout>
</template>
