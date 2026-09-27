import { computed, ref, unref, type ComputedRef, type Ref } from 'vue'
import { Dropdown } from '@/objects/Dropdown'
import { useConstants } from '@/composables/useConstants'
import type { InputValue, OptionItem } from '@/types/input'
import { CustomFilter } from '@/types/filter'

type MaybeRef<T> = T | Ref<T> | ComputedRef<T>

type UseSelectReturn = {
  items: ComputedRef<OptionItem[]>
  selectedItems: ComputedRef<OptionItem|OptionItem[]|undefined>
  selectionIcon: ComputedRef<string>
  getItemTitle: (item: OptionItem) => string
}

export default function useSelect(
  value: MaybeRef<InputValue>,
  src: string,
  multiple: MaybeRef<boolean> = false,
  search: MaybeRef<string | undefined> = undefined,
  customFilter?: CustomFilter,
  customOptions?: MaybeRef<OptionItem[] | undefined>
): UseSelectReturn {
  const searchRef = ref(search)
  const customOptionsRef = ref(customOptions)
  const { getConstants } = useConstants()

  const items = computed<OptionItem[]>(() => {
    const items = customOptionsRef.value || getConstants(src)

    return Dropdown.filterAndOrder(items, searchRef.value ?? '', customFilter)
  })

  const selectedItems = computed(() =>
    Dropdown.selectedItems(items.value, String(unref(value)), unref(multiple))
  )

  const selectionIcon = computed(() =>
    Dropdown.selectionIcon(items.value, String(unref(value)), unref(multiple))
  )

  const getItemTitle = (item: OptionItem) => Dropdown.getItemTitle(item)

  return {
    items,
    selectedItems,
    selectionIcon,
    getItemTitle,
  }
}
