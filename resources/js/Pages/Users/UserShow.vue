<script lang="ts" setup>
import { onBeforeUnmount, watch } from 'vue'
import { storeToRefs } from 'pinia'
import UserInfo from '@/Components/Info/UserInfo.vue'
import ShowLayout from '@/Layouts/ShowLayout.vue'
import { useUserStore } from '@/stores/models/userStore'
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
  <ShowLayout
    v-if="currentUser"
    :title="currentUser.name"
    index-route="users.index"
  >
    <UserInfo :user="currentUser" />
  </ShowLayout>
</template>
