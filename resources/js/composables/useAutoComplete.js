import useHttp from '@/composables/useHttp'
import { ref, watch } from 'vue'
import Dropdown from '@/objects/Dropdown.js'
import useConstants from '@/composables/useConstants.js'
import QueryString from '@/objects/QueryString.js'

export default (
  userInput,
  model,
  multiple = false,
  search = null,
  params = {},
  urlAttribute = 'name'
) => {
  const { getJson, loading } = useHttp()
  model = ref(model)
  params = ref(params)
  search = ref(search)
  userInput = ref(userInput)
  multiple = ref(multiple)

  const items = ref([])
  const selectedItems = ref(null)
  const selectionIcon = ref(null)
  let lastStringParams = null
  let initialRequest = false

  watch(search, () => {
    if (!search.value?.trim()) return
    const paramsValue = { ...params.value }

    if (search.value) {
      paramsValue[urlAttribute] = search.value
    }

    makeRequest(model.value, paramsValue)
  })

  watch(
    params,
    () => {
      const paramsValue = { ...params.value }

      if (userInput.value && !initialRequest) {
        initialRequest = true
        const paramsData = getPreParams(
          userInput.value,
          multiple.value,
          urlAttribute,
          model.value
        )

        paramsValue[paramsData.urlAttribute] = paramsData.urlValue
      }

      makeRequest(model.value, paramsValue)
    },
    { immediate: true }
  )

  watch(
    userInput,
    (newValue, oldValue) => {
      if (newValue === oldValue) return

      if (newValue) {
        selectedItems.value = Dropdown.selectedItems(
          items.value,
          newValue,
          multiple.value
        )
        selectionIcon.value = Dropdown.selectionIcon(
          items.value,
          newValue,
          multiple.value
        )
      } else {
        selectedItems.value = null
        selectionIcon.value = null
        makeRequest(model.value, { ...params.value })
      }
    },
    { immediate: true }
  )

  async function makeRequest(model, params = {}, force = false) {
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
function getPreParams(value, multiple, urlAttribute, model) {
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
