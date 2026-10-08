<script setup lang="ts">
import AppForm from '@/Components/Core/AppForm.vue'
import iSelect from '@/Components/Inputs/iSelect.vue'
import iText from '@/Components/Inputs/iText.vue'
import useForm from '@/composables/useForm'
import { Translator } from '@/objects/Translator'
import { User } from '@/types'

const props = defineProps<{ user?: User | null }>()
const emit = defineEmits<{ success: []; cancel: [] }>()

const { form } = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? '',
  role: props.user?.role ?? 'user',
  language: props.user?.language ?? (undefined as string | undefined),
  currency: props.user?.currency ?? (undefined as string | undefined),
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
    form.put(route('users.update', props.user.id), options)
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
