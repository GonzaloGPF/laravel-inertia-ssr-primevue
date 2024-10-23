<script setup>
import Translator from '@/objects/Translator.js'
import RegisterForm from '@/Components/Forms/RegisterForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'
import AppLogo from '@/Components/Core/AppLogo.vue'
</script>
<template>
  <CardLayout :title="Translator.actionTitle('register')" class="max-w-7xl">
    <AppLogo class="mb-5" />
    <RegisterForm />
  </CardLayout>
</template>
