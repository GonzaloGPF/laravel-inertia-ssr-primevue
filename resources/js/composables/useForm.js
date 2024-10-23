import { useForm as inertiaUseForm } from '@inertiajs/vue3'

export default function useForm(data = {}) {
  const form = inertiaUseForm(data)

  // const defaultOptions = {
  //     preserveScroll: true,
  //     onSuccess: () => form.reset(),
  // }

  return {
    form,
  }
}
