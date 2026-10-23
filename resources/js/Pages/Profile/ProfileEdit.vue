<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Translator } from '@/objects/Translator'
import AppCard from '@/Components/Core/AppCard.vue'
import ProfileForm from '@/Components/Forms/ProfileForm.vue'
import ProfilePasswordForm from '@/Components/Forms/ProfilePasswordForm.vue'
import ProfileDeleteForm from '@/Components/Forms/ProfileDeleteForm.vue'

type Props = {
  mustVerifyEmail: boolean
  status: string
}

defineProps<Props>()
</script>
<template>
  <div class="flex flex-col gap-6 pb-6">
    <Head :title="Translator.tl('profile')" />
    <AppCard
      :title="Translator.tl('profile_info')"
      :description="Translator.t('help.profile_info')"
      icon="person"
    >
      <ProfileForm :must-verify-email="mustVerifyEmail" />
    </AppCard>
    <AppCard
      :title="Translator.tl('update_password')"
      :description="Translator.t('help.update_password')"
      icon="lock"
    >
      <ProfilePasswordForm />
    </AppCard>
    <AppCard
      :title="Translator.tl('danger_zone')"
      :description="Translator.t('help.danger_zone')"
      icon="warning"
      severity="danger"
    >
      <ProfileDeleteForm />
    </AppCard>
  </div>
</template>
