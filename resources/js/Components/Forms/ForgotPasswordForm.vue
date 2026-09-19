<script setup lang="ts">
import useForm from '@/composables/useForm.js'
import { Translator } from '@/objects/Translator'
import AppForm from '@/Components/Core/AppForm.vue'
import iText from '@/Components/Inputs/iText.vue'

const { form } = useForm({
  email: '',
})
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('reset')"
    :loading="form.processing"
    @submit="form.post(route('password.email'))"
  >
    <iText
      v-model="form.email"
      :error="form.errors.email"
      name="email"
      required
      autocomplete
    />
  </AppForm>
</template>
