<script setup lang="ts">
import AppForm from '@/Components/Core/AppForm.vue'
import iImage from '@/Components/Inputs/iImage.vue'
import iSelect from '@/Components/Inputs/iSelect.vue'
import iText from '@/Components/Inputs/iText.vue'
import useForm from '@/composables/useForm.js'
import useAuth from '@/composables/useAuth'
import { Translator } from '@/objects/Translator'
import AppField from '@/Components/Core/AppField.vue'

// TODO: use this
defineProps<{ mustVerifyEmail?: boolean }>()
const AVATAR_TYPES = 'image/jpeg,image/png,image/webp'

const { user } = useAuth()

const { form } = useForm({
  name: user.value?.name,
  email: user.value?.email,
  language: user.value?.language,
  currency: user.value?.currency,
  avatar: null as File | null,
  remove_avatar: false,
})
const onSubmit = () => {
  // A file cannot go in a PUT request: it is sent as a POST that Laravel handles as a PUT
  form
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(route('profile.update'), {
      onSuccess: ({ props }) => {
        form.defaults(props.auth.user)
        form.reset('avatar', 'remove_avatar')
        Translator.setLocale(props.auth.user.language)
      },
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
    <iImage
      v-model="form.avatar"
      v-model:removed="form.remove_avatar"
      :current="user?.avatar_url"
      :fallback="user?.name"
      :error="form.errors.avatar"
      :accept="AVATAR_TYPES"
      name="avatar"
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
