<script setup>
import AppForm from '@/Components/Core/AppForm.vue'
import useForm from '@/composables/useForm.js'
import Translator from '@/objects/Translator.js'
import IPassword from '@/Components/Inputs/iPassword.vue'
import { ref } from 'vue'
import AppField from '@/Components/Core/AppField.vue'

const passwordInput = ref(null)
const currentPasswordInput = ref(null)

const { form } = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put(route('password.update'), {
    onError: () => {
      if (form.errors.current_password) {
        form.reset('current_password')
        currentPasswordInput.value.focus()
      }
      if (form.errors.password) {
        form.reset('password', 'password_confirmation')
        passwordInput.value.focus()
      }
    },
  })
}
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('save')"
    :loading="form.processing"
    @submit="submit"
  >
    <AppField
      :label="Translator.tl('update_password')"
      :model-value="Translator.t('help.update_password')"
    />
    <i-password
      v-model="form.current_password"
      :error="form.errors.current_password"
      :autocomplete="true"
      prepend-icon="$password"
      name="current_password"
    />
    <i-password
      v-model="form.password"
      :error="form.errors.password"
      :label="Translator.tl('new_password')"
      prepend-icon="$password"
      name="password"
    />
    <i-password
      v-model="form.password_confirmation"
      :error="form.errors.password_confirmation"
      prepend-icon="$password"
      name="password_confirmation"
    />
  </AppForm>
</template>
