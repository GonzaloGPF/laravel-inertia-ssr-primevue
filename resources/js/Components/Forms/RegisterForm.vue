<script setup lang="ts">
import iText from '@/Components/Inputs/iText.vue'
import iPassword from '@/Components/Inputs/iPassword.vue'
import useForm from '@/composables/useForm.js'
import { Translator } from '@/objects/Translator'
import AppForm from '@/Components/Core/AppForm.vue'
import { computed } from 'vue'

const { form } = useForm({
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}

const secondaryButton = computed(() => ({
  label: Translator.tl('already_registered'),
  href: route('login'),
}))
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('register')"
    :loading="form.processing"
    :secondary-button="secondaryButton"
    @submit="submit"
  >
    <iText
      v-model="form.email"
      :error="form.errors.email"
      name="email"
      prepend-icon="$email"
      required
      autofocus
      autocomplete
    />
    <iPassword
      v-model="form.password"
      :error="form.errors.password"
      name="password"
      required
    />
    <iPassword
      v-model="form.password_confirmation"
      :error="form.errors.password_confirmation"
      name="password_confirmation"
      required
    />
  </AppForm>
</template>
