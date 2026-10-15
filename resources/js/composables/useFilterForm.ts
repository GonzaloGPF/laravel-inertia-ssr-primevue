import {
  computed,
  onBeforeUnmount,
  onMounted,
  ref,
  toValue,
  watch,
  type MaybeRefOrGetter,
} from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { debounce, isEqual, omit, pick } from 'lodash-es'
import { Filter, type FilterInput } from '@/objects/Filter'
import { QueryString, type SearchValues } from '@/objects/QueryString'
import { Translator } from '@/objects/Translator'
import type { FilterField, FilterFormValues, FilterValue } from '@/types/filter'

const DEBOUNCE_MS = 400

/**
 * State of a filters form. The url query is the source of truth: the form is
 * initialised from it, and every change in the form (debounced) is written back
 * to it, resetting the pagination. When the url changes (i.e. browser back and
 * forward) the form follows it.
 *
 * `onInput` is called, with the values written in the url, after each change.
 */
export default function useFilterForm(
  fields: MaybeRefOrGetter<FilterField[]>,
  onInput?: (values: SearchValues) => void
) {
  const page = usePage()
  const path = computed(() => page.url.split('?')[0])
  const search = computed(() => QueryString.parseSearch(page.url.split('?')[1]))

  const inputs = computed(() => Filter.processFields(toValue(fields)))
  const ownedKeys = computed(() => inputs.value.flatMap(Filter.keys))

  const form = ref<FilterFormValues>(Filter.toForm(inputs.value, search.value))
  const values = computed(() => Filter.serialize(inputs.value, form.value))

  const filterText = computed(() =>
    inputs.value
      .filter((input) => Filter.keys(input).some((key) => key in values.value))
      .map(getLabel)
      .join(', ')
  )

  let mountedPath = ''
  let pending = false

  const updateQuery = () => {
    pending = false

    const current = pick(search.value, ownedKeys.value)

    if (path.value !== mountedPath || isEqual(values.value, current)) {
      return
    }

    // The pagination starts over, as the filtered items are other ones
    const rest = omit(search.value, [...ownedKeys.value, 'page'])

    router.replace({
      url: `${path.value}?${QueryString.toSearch({ ...rest, ...values.value })}`,
      preserveState: true,
      preserveScroll: true,
    })

    onInput?.(values.value)
  }

  const debouncedUpdateQuery = debounce(updateQuery, DEBOUNCE_MS)

  const setValue = (input: FilterInput, value: FilterValue) => {
    pending = true
    form.value = Filter.setValue(input, form.value, value)
    debouncedUpdateQuery()
  }

  const reset = () => {
    debouncedUpdateQuery.cancel()
    form.value = {}
    updateQuery()
  }

  watch(
    () => page.url,
    () => {
      // A change not written yet by the user is the one that has to prevail
      if (pending || path.value !== mountedPath) {
        return
      }

      const fromUrl = Filter.toForm(inputs.value, search.value, false)

      if (!isEqual(Filter.serialize(inputs.value, fromUrl), values.value)) {
        form.value = fromUrl
      }
    }
  )

  onMounted(() => {
    mountedPath = path.value
    updateQuery() // Writes the preset values the url doesn't have yet
  })

  onBeforeUnmount(() => debouncedUpdateQuery.cancel())

  return { inputs, form, filterText, setValue, reset }
}

function getLabel(input: FilterInput): string {
  if (typeof input.label === 'function') {
    return input.label()
  }

  return input.label ?? Translator.ta(input.name)
}
