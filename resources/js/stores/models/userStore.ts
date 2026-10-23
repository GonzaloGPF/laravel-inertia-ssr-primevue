import { defineStore } from 'pinia'
import { ref } from 'vue'
import { User } from '@/types'

export const useUserStore = defineStore('user', () => {
  /** The user shown in the detail view. The components of that view read it from here. */
  const currentUser = ref<User | null>(null)

  return {
    currentUser,
  }
})
