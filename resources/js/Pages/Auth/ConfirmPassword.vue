<script setup>
import Translator from '@/objects/Translator.js'
import ConfirmPasswordForm from '@/Components/Forms/ConfirmPasswordForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'
</script>
<template>
  <CardLayout
    :title="Translator.tl('confirm_password')"
    :description="Translator.t('help.confirm_password')"
    class="max-w-7xl"
  >
    <ConfirmPasswordForm />
  </CardLayout>
</template>
