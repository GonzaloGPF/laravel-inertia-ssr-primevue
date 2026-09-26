<script setup lang="ts">
import { Translator } from '@/objects/Translator'
import useForm from '@/composables/useForm'
import iPassword from '@/Components/Inputs/iPassword.vue'
import iText from '@/Components/Inputs/iText.vue'
import AppForm from '@/Components/Core/AppForm.vue'
import { toRefs } from 'vue'

type Props = {
  email: string
  token: string
}

const props = defineProps<Props>()
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
    <iText
      v-model="form.email"
      :error="form.errors.email"
      disabled
      name="email"
      type="email"
      required
      autofocus
      autocomplete
    />
    <iPassword
      v-model="form.password"
      :error="form.errors.password"
      name="new_password"
      required
    />
    <iPassword
      v-model="form.password_confirmation"
      :error="form.errors.password_confirmation"
      name="new_password_confirmation"
      required
    />
  </AppForm>
</template>
