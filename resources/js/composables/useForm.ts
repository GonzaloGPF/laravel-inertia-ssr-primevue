import { useForm as inertiaUseForm, type InertiaForm } from '@inertiajs/vue3'
import type { FormDataConvertible } from '@inertiajs/core'

export default function useForm<TForm extends Record<string, FormDataConvertible>>(
  data: TForm
): { form: InertiaForm<TForm> } {
  // Inertia's useForm adds an internal reserved-key guard to its generic that a
  // bare type parameter can't satisfy, so we assert through it while keeping the
  // strongly-typed InertiaForm<TForm> return.
  const form = inertiaUseForm(data as never) as InertiaForm<TForm>

  return {
    form,
  }
}
