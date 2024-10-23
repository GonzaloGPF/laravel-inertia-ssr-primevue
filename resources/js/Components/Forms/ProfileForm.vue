<script setup>
import AppForm from '@/Components/Core/AppForm.vue'
import ISelect from '@/Components/Inputs/iSelect.vue'
import IText from '@/Components/Inputs/iText.vue'
import useForm from '@/composables/useForm.js'
import useAuth from '@/composables/useAuth.js'
import Translator from '@/objects/Translator.js'
import AppField from '@/Components/Core/AppField.vue'

defineProps({
  mustVerifyEmail: {
    // TODO: use this
    type: Boolean,
  },
})
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
      :model-value="Translator.t('help.profile_info')"
    />
    <i-text
      v-model="form.name"
      :error="form.errors.name"
      name="name"
      required
      autofocus
      autocomplete
    />
    <i-text
      v-model="form.email"
      :error="form.errors.email"
      name="email"
      type="email"
      required
      autofocus
      autocomplete
    />
    <i-select
      v-model="form.language"
      :error="form.errors.language"
      name="language"
      src="languages"
    />
    <i-select
      v-model="form.currency"
      :error="form.errors.currency"
      name="currency"
      src="currencies"
    />
  </AppForm>
</template>
