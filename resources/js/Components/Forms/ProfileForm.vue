<script setup lang="ts">
import AppForm from '@/Components/Core/AppForm.vue'
import iSelect from '@/Components/Inputs/iSelect.vue'
import iText from '@/Components/Inputs/iText.vue'
import useForm from '@/composables/useForm.js'
import useAuth from '@/composables/useAuth'
import { Translator } from '@/objects/Translator'
import AppField from '@/Components/Core/AppField.vue'

// TODO: use this
defineProps<{ mustVerifyEmail?: boolean}>()
const { user } = useAuth()

const { form } = useForm({
  name: user.value?.name,
  email: user.value?.email,
  language: user.value?.language,
  currency: user.value?.currency,
})
const onSubmit = () => {
  form.put(route('profile.update'), {
    onSuccess: ({ props }) => form.defaults(props.auth.user),
  })
}
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('save')"
    :loading="form.processing"
    @submit="onSubmit"
  >
    <AppField
      :label="Translator.tl('profile_info')"
      :value="Translator.t('help.profile_info')"
    />
    <iText
      v-model="form.name"
      :error="form.errors.name"
      name="name"
      required
      autofocus
      autocomplete="name"
    />
    <iText
      v-model="form.email"
      :error="form.errors.email"
      name="email"
      type="email"
      required
      autofocus
      autocomplete="email"
    />
    <iSelect
      v-model="form.language"
      :error="form.errors.language"
      name="language"
      src="languages"
    />
    <iSelect
      v-model="form.currency"
      :error="form.errors.currency"
      name="currency"
      src="currencies"
    />
  </AppForm>
</template>
