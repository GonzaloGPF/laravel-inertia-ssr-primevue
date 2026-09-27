<script setup lang="ts">
import AppForm from '@/Components/Core/AppForm.vue'
import useForm from '@/composables/useForm.js'
import { Translator } from '@/objects/Translator'
import iPassword from '@/Components/Inputs/iPassword.vue'
import { useTemplateRef } from 'vue'
import AppField from '@/Components/Core/AppField.vue'

const passwordInput = useTemplateRef<HTMLInputElement>('passwordInput')
const currentPasswordInput = useTemplateRef<HTMLInputElement>(
  'currentPasswordInput'
)

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
        currentPasswordInput.value?.focus()
      }
      if (form.errors.password) {
        form.reset('password', 'password_confirmation')
        passwordInput.value?.focus()
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
      :value="Translator.t('help.update_password')"
    />
    <iPassword
      ref="currentPasswordInput"
      v-model="form.current_password"
      :error="form.errors.current_password"
      autocomplete="current-password"
      prepend-icon="$password"
      name="current_password"
    />
    <iPassword
      ref="passwordInput"
      v-model="form.password"
      :error="form.errors.password"
      :label="Translator.tl('new_password')"
      prepend-icon="$password"
      name="password"
      autocomplete="new-password"
    />
    <iPassword
      v-model="form.password_confirmation"
      :error="form.errors.password_confirmation"
      prepend-icon="$password"
      name="password_confirmation"
      autocomplete="new-password"
    />
  </AppForm>
</template>
