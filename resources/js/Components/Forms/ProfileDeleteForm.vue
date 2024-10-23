<script setup>
import { computed, ref } from 'vue'
import AppButton from '@/Components/Core/AppButton.vue'
import Translator from '@/objects/Translator.js'
import useForm from '@/composables/useForm.js'
import IPassword from '@/Components/Inputs/iPassword.vue'

const openModal = ref(false)
const passwordInput = ref(null)

const { form } = useForm({
  password: '',
})

const modal = computed(() => ({
  data: {
    modelValue: openModal.value,
    title: Translator.t('help.delete_account'),
    loading: form.processing,
    okText: Translator.actionTitle('delete'),
    okAspect: 'danger',
  },
  listeners: {
    close: closeModal,
    ok: deleteUser,
  },
}))

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onFinish: () => form.reset(),
  })
}

const closeModal = () => {
  openModal.value = false

  form.reset()
}
</script>

<template>
  <AppButton
    :label="Translator.actionTitle('delete')"
    :modal="modal"
    aspect="danger"
    @click="openModal = true"
  >
    <template #modal>
      <p
        class="my-3 text-sm"
        v-text="Translator.t('help.delete_account_warning')"
      />
      <i-password
        ref="passwordInput"
        v-model="form.password"
        :error="form.errors.password"
        :autocomplete="true"
        :autofocus="true"
        required
        prepend-icon="$password"
        name="password"
        @keyup.enter="deleteUser"
      />
    </template>
  </AppButton>
</template>
