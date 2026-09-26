<script setup>
import { router } from '@inertiajs/vue3'
import { Translator } from '@/objects/Translator'
import useForm from '@/composables/useForm.ts'
import AppForm from '@/Components/Core/AppForm.vue'
import { computed } from 'vue'

const { form } = useForm({})

const secondaryButton = computed(() => ({
  label: Translator.tl('logout'),
}))

const logout = () => {
  router.post(route('logout'))
}
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('resend')"
    :loading="form.processing"
    :secondary-button="secondaryButton"
    @submit="form.post(route('verification.send'))"
    @secondary="logout"
  />
</template>
