<script setup>
import Translator from '@/objects/Translator.js'
import ProfileForm from '@/Components/Forms/ProfileForm.vue'
import ProfilePasswordForm from '@/Components/Forms/ProfilePasswordForm.vue'
import ProfileDeleteForm from '@/Components/Forms/ProfileDeleteForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

defineProps({
  mustVerifyEmail: {
    type: Boolean,
  },
  status: {
    type: String,
  },
})
</script>
<template>
  <CardLayout :title="Translator.tl('profile')" icon="$profile">
    <ProfileForm :must-verify-email="mustVerifyEmail" />
    <ProfilePasswordForm class="my-8" />
    <ProfileDeleteForm class="flex max-w-xl ml-auto mt-8" />
  </CardLayout>
</template>
