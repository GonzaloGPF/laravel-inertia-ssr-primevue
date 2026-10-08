<script setup lang="ts">
import AppForm from '@/Components/Core/AppForm.vue'
import iSelect from '@/Components/Inputs/iSelect.vue'
import iText from '@/Components/Inputs/iText.vue'
import useForm from '@/composables/useForm'
import { Translator } from '@/objects/Translator'

const emit = defineEmits<{ success: []; cancel: [] }>()

const { form } = useForm({
  name: '',
  email: '',
  phone: '',
  role: 'user',
  language: undefined as string | undefined,
  currency: undefined as string | undefined,
})

const secondaryButton = {
  label: Translator.actionTitle('cancel'),
  text: true,
}

const onSubmit = () => {
  form.post(route('users.store'), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => emit('success'),
  })
}
</script>
<template>
  <AppForm
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
