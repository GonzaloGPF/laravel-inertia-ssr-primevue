import { computed, nextTick, ref } from 'vue'
import Filter from '@/objects/Filter'
import Utils from '@/objects/Utils.js'
import Translator from '@/objects/Translator.js'
import { router, useForm } from '@inertiajs/vue3'
import QueryString from '../objects/QueryString.js'
import lodash from 'lodash'

export default (fields) => {
  const query = QueryString.parse(window.location.search.replace('?', ''))

  fields = ref(fields)

  const inputs = computed(() => Filter.processFields(fields.value, query))

  const { form } = useForm(buildFormData(inputs.value))

  const filterText = computed(() => getFilterText(form.data(), inputs.value))
  const loading = computed(() => form.processing)

  const search = (extraParams) => {
    const path = window.location.pathname
    const formData = Utils.removeEmpty(form.data())
    const data = {
      ...formData,
      ...extraParams,
    }

    router.visit(path, {
      only: ['data'],
      preserveScroll: true,
      data,
    })
  }

  function reset(inputRefs) {
    inputRefs.forEach((inputRef) => inputRef.reset())
    // inputs.value.forEach(input => {
    //   form[input.name] = null
    // })
    nextTick(() => search())
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

function buildFormData(inputs) {
  const inputEntries = inputs.map((input) => [input.name, input.value])

  return Object.fromEntries(inputEntries)
}

function getFilterText(form, inputs) {
  return lodash
    .chain(form)
    .keys()
    .filter((key) => !Utils.isEmptyValue(form[key]) && key !== 'page')
    .map((key) => getInputLabel(inputs.find((input) => input.name === key)))
    .uniq()
    .join(', ')
    .value()
}

function getInputLabel(input) {
  if (!input.label) {
    return Translator.ta(input.name)
  }
  if (typeof input.label === 'function') {
    return input.label()
  }
  return input.label
}
