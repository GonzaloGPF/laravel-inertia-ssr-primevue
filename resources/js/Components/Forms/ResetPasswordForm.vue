<script setup>
import Translator from '@/objects/Translator.js'
import useForm from '@/composables/useForm.js'
import IPassword from '@/Components/Inputs/iPassword.vue'
import IText from '@/Components/Inputs/iText.vue'
import AppForm from '@/Components/Core/AppForm.vue'
import { toRefs } from 'vue'

const props = defineProps({
  email: {
    type: String,
    required: true,
  },
  token: {
    type: String,
    required: true,
  },
})
const { token, email } = toRefs(props)
const { form } = useForm({
  token: token.value,
  email: email.value,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('reset')"
    :loading="form.processing"
    @submit="submit"
  >
    <i-text
      v-model="form.email"
      :error="form.errors.email"
      disabled
      name="email"
      type="email"
      required
      autofocus
      autocomplete
    />
    <i-password
      v-model="form.password"
      :error="form.errors.password"
      name="new_password"
      required
    />
    <i-password
      v-model="form.password_confirmation"
      :error="form.errors.password_confirmation"
      name="new_password_confirmation"
      required
    />
  </AppForm>
</template>
