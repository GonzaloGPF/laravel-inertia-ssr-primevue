<script setup lang="ts">
import AppForm from '@/Components/Core/AppForm.vue'
import iImage from '@/Components/Inputs/iImage.vue'
import iNumber from '@/Components/Inputs/iNumber.vue'
import iSelect from '@/Components/Inputs/iSelect.vue'
import iText from '@/Components/Inputs/iText.vue'
import useForm from '@/composables/useForm'
import { Translator } from '@/objects/Translator'
import { User } from '@/types'

const AVATAR_TYPES = 'image/jpeg,image/png,image/webp'

const props = defineProps<{ user?: User | null }>()
const emit = defineEmits<{ success: []; cancel: [] }>()

const { form } = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? '',
  age: props.user?.age ?? (undefined as number | string | undefined),
  role: props.user?.role ?? 'user',
  language: props.user?.language ?? (undefined as string | undefined),
  currency: props.user?.currency ?? (undefined as string | undefined),
  avatar: null as File | null,
  remove_avatar: false,
})

const secondaryButton = {
  label: Translator.actionTitle('cancel'),
  text: true,
}

const onSubmit = () => {
  const options = {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => emit('success'),
  }

  if (props.user?.id) {
    // A file cannot go in a PUT request: it is sent as a POST that Laravel handles as a PUT
    form
      .transform((data) => ({ ...data, _method: 'put' }))
      .post(route('users.update', props.user.id), options)
  } else {
    form.post(route('users.store'), options)
  }
}
</script>
<template>
  <AppForm
    :model="user ?? undefined"
    :loading="form.processing"
    :secondary-button="secondaryButton"
    @submit="onSubmit"
    @secondary="emit('cancel')"
  >
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
      fluid
    />
    <iText
      v-model="form.email"
      :error="form.errors.email"
      name="email"
      type="email"
      required
      fluid
    />
    <iText
      v-model="form.phone"
      :error="form.errors.phone"
      name="phone"
      type="tel"
      fluid
    />
    <iNumber
      v-model="form.age"
      :error="form.errors.age"
      name="age"
      :min="0"
      :max="150"
      fluid
    />
    <iSelect
      v-model="form.role"
      :error="form.errors.role"
      name="role"
      src="roles"
      required
      fluid
    />
    <iSelect
      v-model="form.language"
      :error="form.errors.language"
      name="language"
      src="languages"
      fluid
    />
    <iSelect
      v-model="form.currency"
      :error="form.errors.currency"
      name="currency"
      src="currencies"
      fluid
    />
  </AppForm>
</template>
