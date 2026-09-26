<script setup lang="ts">
import iText from '@/Components/Inputs/iText.vue'
import iPassword from '@/Components/Inputs/iPassword.vue'
import useForm from '@/composables/useForm.js'
import { Translator } from '@/objects/Translator'
import AppForm from '@/Components/Core/AppForm.vue'
import useAuth from '@/composables/useAuth'
import { Link } from '@inertiajs/vue3'
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
  <div>
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
        autocomplete="username"
      />
      <iPassword
        v-model="form.password"
        :error="form.errors.password"
        name="password"
        autocomplete="current-password"
      />
    </AppForm>
    <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
      {{ Translator.tl('no_account') }}
      <Link
        :href="route('register')"
        class="text-indigo-600 underline hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
      >
        {{ Translator.tl('create_account_here') }}
      </Link>
    </p>
  </div>
</template>
