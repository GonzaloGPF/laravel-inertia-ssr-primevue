<script setup>
import iText from '@/Components/Inputs/iText.vue'
import iPassword from '@/Components/Inputs/iPassword.vue'
import useForm from '@/composables/useForm.js'
import Translator from '@/objects/Translator.js'
import AppForm from '@/Components/Core/AppForm.vue'
import useAuth from '@/composables/useAuth.js'
import { computed } from 'vue'

const { form } = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => {
      form.reset('password')
      Translator.setLocale(useAuth().user.value?.language)
    },
  })
}

const secondaryButton = computed(() => ({
  label: Translator.tl('forgot_password'),
  href: route('password.request'),
}))
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('login')"
    :loading="form.processing"
    :secondary-button="secondaryButton"
    @submit="submit"
  >
    <iText
      v-model="form.email"
      :error="form.errors.email"
      name="email"
      type="email"
      prepend-icon="$email"
      autofocus
      autocomplete
    />
    <iPassword
      v-model="form.password"
      :error="form.errors.password"
      name="password"
      autocomplete
    />
  </AppForm>
</template>
