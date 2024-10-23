import { router, usePage } from '@inertiajs/vue3'
import { computed, toRefs } from 'vue'
import Translator from '@/objects/Translator.js'
import { useFlashMessages } from '@/stores/flashMessages.js'

export default function useAuth() {
  const { props } = toRefs(usePage())

  const user = computed(() => props.value.auth.user)
  const isLogged = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isUser = computed(() => user.value?.role === 'user')

  const hasRole = (role, customUser = null) => {
    if (!Array.isArray(role)) {
      role = [role]
    }
    if (customUser !== null) {
      return role.includes(customUser?.role)
    }
    return role.includes(user.value?.role)
  }

  const updateLanguage = (language) => {
    Translator.setLocale(language)

    if (isLogged.value) {
      let cancelToken

      router.put(
        route('profile.update'),
        { language, quietly: true },
        {
          onCancelToken: (token) => (cancelToken = token),
          onFinish: () =>
            useFlashMessages().pushFlashMessageAction('updated', 'user'),
        }
      )

      cancelToken.cancel()
    }
  }
  const createdByMe = (model) => model?.created_by?.id === user.value?.id

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
