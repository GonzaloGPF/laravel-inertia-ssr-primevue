import { router, usePage } from '@inertiajs/vue3'
import { computed, toRefs } from 'vue'
import { Translator } from '@/objects/Translator'
import { useFlashMessages } from '@/stores/flashMessages.js'
import { User } from '@/types'
import { BaseModel } from '@/types/base-model'

export default function useAuth() {
  const { props } = toRefs(usePage())

  const user = computed(() => props.value.auth.user)
  const isLogged = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isUser = computed(() => user.value?.role === 'user')

  const hasRole = (role: string | string[], customUser?: User) => {
    if (!Array.isArray(role)) {
      role = [role]
    }
    if (customUser !== null) {
      return role.includes(customUser?.role || '')
    }
    return role.includes(user.value?.role as string)
  }

  const updateLanguage = (language: string) => {
    Translator.setLocale(language)

    if (isLogged.value) {
      router.put(
        route('profile.update'),
        { language, quietly: true },
        {
          preserveScroll: true,
          preserveState: true,
          replace: true,
          onFinish: () => {
            useFlashMessages().pushFlashMessageAction('updated')
          },
        }
      )
    }
  }
  const createdByMe = (model?: BaseModel) =>
    model?.created_by === user.value?.id

  return {
    isLogged,
    isAdmin,
    isUser,
    user,
    updateLanguage,
    hasRole,
    createdByMe,
  }
}
