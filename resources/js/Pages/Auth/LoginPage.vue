<script setup>
import AppLogo from '@/Components/Core/AppLogo.vue'
import LoginForm from '@/Components/Forms/LoginForm.vue'
import Translator from '@/objects/Translator.js'
import CardLayout from '@/Layouts/CardLayout.vue'
</script>
<template>
  <CardLayout :title="Translator.actionTitle('login')">
    <div class="flex flex-col justify-center">
      <AppLogo class="my-5" />
      <LoginForm />
    </div>
  </CardLayout>
</template>
