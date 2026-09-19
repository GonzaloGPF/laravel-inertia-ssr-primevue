<script setup lang="ts">
import { Translator } from '@/objects/Translator'
import ProfileForm from '@/Components/Forms/ProfileForm.vue'
import ProfilePasswordForm from '@/Components/Forms/ProfilePasswordForm.vue'
import ProfileDeleteForm from '@/Components/Forms/ProfileDeleteForm.vue'
import CardLayout from '@/Layouts/CardLayout.vue'

type Props = {
  mustVerifyEmail: boolean
  status: string
}

defineProps<Props>()
</script>
<template>
  <CardLayout :title="Translator.tl('profile')" icon="$profile">
    <ProfileForm :must-verify-email="mustVerifyEmail" />
    <ProfilePasswordForm class="my-8" />
    <ProfileDeleteForm class="flex max-w-xl ml-auto mt-8" />
  </CardLayout>
</template>
