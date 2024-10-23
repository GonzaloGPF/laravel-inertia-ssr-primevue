<script setup>
import Translator from '@/objects/Translator.js'
import useForm from '@/composables/useForm.js'
import AppForm from '@/Components/Core/AppForm.vue'
import { computed } from 'vue'

const { form } = useForm({})

const secondaryButton = computed({
  label: Translator.tl('logout'),
  href: route('verification.send'),
})
</script>
<template>
  <AppForm
    :label="Translator.actionTitle('resend')"
    :loading="form.processing"
    :secondary-button="secondaryButton"
    @submit="form.post(route('verification.send'))"
  />
</template>
