import useHttp from '@/composables/useHttp'
import { ref, watch, type Ref } from 'vue'
import { Dropdown } from '@/objects/Dropdown'
import { useConstants } from '@/composables/useConstants'
import { QueryString } from '@/objects/QueryString'
import { InputValue, OptionItem, QueryParams } from '@/types/input'

type Options = {
  multiple?: boolean
  params?: QueryParams
  urlAttribute?: string
}

export default (
  userInput: Ref<InputValue>,
  model: Ref<string | undefined>,
  search: Ref<string>,
  options: Ref<Options> = ref({}),
) => {
  const { getJson, loading } = useHttp()

  const items = ref<OptionItem[]>([])
  const selectedItems = ref<OptionItem[] | OptionItem | undefined>([])
  const selectionIcon = ref('')
  let lastStringParams = ''
  let initialRequest = false

  watch(search, () => {
    if (!search.value?.trim()) return
    const optionsValue = options.value || {}
    const urlAttribute = optionsValue.urlAttribute || 'name'

    const paramsValue = { ...(optionsValue.params || {}) }

    if (search.value) {
      paramsValue[urlAttribute] = search.value
    }

    makeRequest(model.value, paramsValue)
  })

  watch(
    () => options.value.params,
    () => {
      const optionsValue = options?.value || {}
      const paramsValue = { ...(optionsValue.params || {}) }

      if (userInput.value && !initialRequest) {
        initialRequest = true
        const paramsData = getPreParams(
          String(userInput.value),
          optionsValue.multiple || false,
          optionsValue.urlAttribute || '',
          model.value
        )

        paramsValue[String(paramsData.urlAttribute)] = paramsData.urlValue
      }

      makeRequest(model.value, paramsValue)
    },
    { immediate: true }
  )

  watch(
    userInput,
    (newValue, oldValue) => {
      if (newValue === oldValue) return

      const optionsValue = options.value

      if (newValue) {
        selectedItems.value = Dropdown.selectedItems(
          items.value,
          String(newValue),
          optionsValue.multiple
        )
        selectionIcon.value = Dropdown.selectionIcon(
          items.value,
          String(newValue),
          optionsValue.multiple
        )
      } else {
        selectedItems.value = []
        selectionIcon.value = ''
        makeRequest(model.value, { ...(optionsValue.params || {}) })
      }
    },
    { immediate: true }
  )

  async function makeRequest(model?: string, params = {}, force = false) {
    if (!model) {
      return;
    }
    const stringParams = QueryString.stringify(params)

    if (!force && lastStringParams === stringParams) {
      return Promise.resolve(items.value)
    }

    lastStringParams = stringParams

    const { data } = await getJson(`api/autocomplete/${model}?${stringParams}`)

    items.value = data.data
  }

  return {
    loading,
    items,
    selectedItems,
    selectionIcon,
  }
}

/**
 * Prepare params when prefilled
 */
function getPreParams(value: string, multiple: boolean, urlAttribute: string, model?: string) {
  const urlValue = multiple ? Object.values(value) : value

  const isConstant = useConstants().existsConstant(model)

  if (!isConstant) {
    urlAttribute = multiple ? 'ids' : 'id'
  }

  return {
    urlAttribute,
    urlValue,
  }
}
