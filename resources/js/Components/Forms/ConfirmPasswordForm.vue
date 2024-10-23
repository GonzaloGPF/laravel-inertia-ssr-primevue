<script setup>
import IPassword from '@/Components/Inputs/iPassword.vue'
import useForm from '@/composables/useForm.js'
import Translator from '@/objects/Translator.js'
import AppForm from '@/Components/Core/AppForm.vue'

const { form } = useForm({
  password: '',
})
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('confirm')"
    :loading="form.processing"
    @submit="form.post(route('password.confirm'))"
  >
    <i-password
      v-model="form.password"
      :error="form.errors.password"
      name="password"
      required
      autocomplete
    />
  </AppForm>
</template>
