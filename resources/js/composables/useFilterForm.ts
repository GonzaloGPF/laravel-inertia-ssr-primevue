import { computed, nextTick, type Ref } from 'vue'
import { Filter } from '@/objects/Filter'
import { Utils } from '@/objects/Utils'
import { Translator } from '@/objects/Translator'
import { router } from '@inertiajs/vue3'
import { QueryString } from '@/objects/QueryString'
import { chain } from 'lodash-es'
import type { FilterField } from '@/types/filter'
import { FormDataConvertible } from '@inertiajs/core'
import useForm from '@/composables/useForm'
import { QueryParams } from '@/types/input'

type FilterInput = {
  name: string
  value: unknown
  label?: string | null | (() => string)
  reset?: () => void
}

type FilterForm = Record<string, FormDataConvertible>

export default function useFilterForm(fields: Ref<FilterField[]>) {
  const query = QueryString.parse(window.location.search.replace('?', ''))

  const inputs = computed<FilterInput[]>(() =>
    Filter.processFields(fields.value, query)
  )

  const { form } = useForm(buildFormData(inputs.value))

  const filterText = computed(() => getFilterText(form.data(), inputs.value))
  const loading = computed(() => form?.processing)

  const search = (extraParams: QueryParams = {}): void => {
    const path = window.location.pathname
    const formData = Utils.removeEmpty(form?.data()) as Record<string, unknown>
    const data: Partial<FormData> = {
      ...formData,
      ...extraParams,
    }

    router.visit(path, {
      only: ['data'],
      preserveScroll: true,
      data: data as FormData,
    })
  }

  function reset(inputRefs: Array<{ reset: () => void }>): void {
    inputRefs.forEach((inputRef) => inputRef.reset())
    // inputs.value.forEach(input => {
    //   form[input.name] = null
    // })
    void nextTick(() => search())
  }

  return {
    inputs,
    form,
    filterText,
    loading,
    reset,
    search,
  }
}

function buildFormData(inputs: FilterInput[]): FilterForm {
  const inputEntries = inputs.map((input) => [input.name, input.value])

  return Object.fromEntries(inputEntries) as FilterForm
}

function getFilterText(form: FilterForm, inputs: FilterInput[]): string {
  return chain(form)
    .keys()
    .filter((key) => !Utils.isEmptyValue(form[key]) && key !== 'page')
    .map((key) => getInputLabel(inputs.find((input) => input.name === key)))
    .uniq()
    .join(', ')
    .value()
}

function getInputLabel(input?: FilterInput): string {
  if (!input) {
    return ''
  }

  if (!input.label) {
    return Translator.ta(input.name)
  }

  if (typeof input.label === 'function') {
    return input.label()
  }

  return input.label
}
