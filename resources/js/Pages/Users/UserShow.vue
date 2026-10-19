<script lang="ts" setup>
import Card from 'primevue/card'
import { onBeforeUnmount, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import AppButton from '@/Components/Core/AppButton.vue'
import UserInfo from '@/Components/Info/UserInfo.vue'
import { Translator } from '@/objects/Translator'
import { useUserStore } from '@/stores/userStore'
import { User } from '@/types'

const props = defineProps<{ user: User }>()
const { currentUser } = storeToRefs(useUserStore())

// What the backend gives is kept in the store, where the components of this view read the user
watch(
  () => props.user,
  (user) => (currentUser.value = user),
  { immediate: true }
)

onBeforeUnmount(() => (currentUser.value = null))
</script>
<template>
  <Card v-if="currentUser">
    <template #title>
      <div class="flex items-center justify-between gap-4">
        <span v-text="currentUser.name" />
        <AppButton
          :label="Translator.actionTitle('back')"
          icon="arrow_back"
          size="small"
          severity="secondary"
          text
          @click="router.visit(route('users.index'))"
        />
      </div>
    </template>
    <template #content>
      <UserInfo :user="currentUser" />
    </template>
  </Card>
</template>
