import { useForm as inertiaUseForm } from '@inertiajs/vue3'
import { FormDataConvertible } from '@inertiajs/core'

type FormDataType = Record<string, FormDataConvertible>

export default function useForm<T extends FormDataType>(data: T) {
  const form = inertiaUseForm(data)

  // const defaultOptions = {
  //     preserveScroll: true,
  //     onSuccess: () => form.reset(),
  // }

  return {
    form,
  }
}
