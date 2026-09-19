import { computed, ref, unref, type Ref } from 'vue'
import { Dropdown } from '@/objects/Dropdown.js'
import { useConstants } from '@/composables/useConstants'
import { OptionItem } from '@/types/input'

type CustomFilterType = (item: OptionItem, search?: string) => boolean

type Options = {
  multiple?: boolean
  customFilter?: CustomFilterType
  customItems?: OptionItem[]
}

export default (
  value: Ref<string | number>,
  src: string,
  search = ref(''),
  options: Ref<Options> = ref({})
) => {
  const items = computed(() => {
    const items = options.value.customItems || useConstants().getConstants(src)

    return Dropdown.filterAndOrder(
      items,
      search.value,
      options.value.customFilter
    )
  })

  const selectedItems = computed(() =>
    Dropdown.selectedItems(items.value, unref(value), !!options.value.multiple)
  )

  const selectionIcon = computed(() =>
    Dropdown.selectionIcon(items.value, unref(value), !!options.value.multiple)
  )

  const getItemTitle = (item: OptionItem) => Dropdown.getItemTitle(item)

  return {
    items,
    selectedItems,
    selectionIcon,
    getItemTitle,
  }
}
