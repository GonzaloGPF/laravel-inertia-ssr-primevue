import { router } from '@inertiajs/vue3'

export default function useNavigationList() {
  const onNavigationClick = (value) => {
    console.log('onNavigationClick', value)
    value = value[0]

    if (!value) {
      return
    }

    if (typeof value === 'function') {
      return value()
    }

    router.visit(value)
  }

  return {
    onNavigationClick,
  }
}