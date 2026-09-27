import { filter, find, orderBy } from 'lodash-es'
import { Utils } from '@/objects/Utils'
import { Translator } from '@/objects/Translator'
import { OptionItem } from '@/types/input'

type SelectedT = string | number
type Filterer = (item: OptionItem, search?: string) => boolean

export const Dropdown = {
  selectedItems(
    items: OptionItem[],
    selected: SelectedT,
    multiple = false
  ): OptionItem | undefined | Array<OptionItem> {
    if (Utils.isEmptyValue(selected)) {
      return multiple ? [] : undefined
    }
    return multiple
      ? filter(items, (item) => String(selected).includes(String(item.value)))
      : (find(items, { id: selected }) as OptionItem)
  },

  selectionIcon(
    items: OptionItem[],
    selectedValue: SelectedT,
    multiple = false
  ): string {
    if (!multiple || !Array.isArray(selectedValue)) {
      return ''
    }

    if (selectedValue.length === items.length) {
      return 'mdi-close-box'
    }

    if (selectedValue.length > 0 && selectedValue.length !== items.length) {
      return 'mdi-minus-box'
    }

    return 'mdi-checkbox-blank-outline'
  },

  filterAndOrder(items: OptionItem[], search: string, customFilter?: Filterer) {
    return orderBy(
      filter(items, (item) => Dropdown.filterItem(item, search, customFilter)),
      ['label', 'name']
    )
  },

  orderItems(items: OptionItem[]) {
    return orderBy(items, ['label', 'name'])
  },

  filterItem(item: OptionItem, search: string, customFilter?: Filterer) {
    if (customFilter) {
      return customFilter(item, search)
    } else {
      return Dropdown.searchItem(item, search)
    }
  },

  searchItem(item: OptionItem, search: string) {
    search = search.toLowerCase().trim()

    let label = item.label || item.name

    if (!label) {
      return false
    }

    if (typeof label === 'function') {
      label = label()
    }

    return label.toLowerCase().includes(search)
  },

  options(values: OptionItem[]) {
    return values.map((value) => ({
      id: value,
      value,
    }))
  },

  getItemTitle(item: OptionItem) {
    if (item.label) {
      return typeof item.label === 'function' ? item.label() : item.label
    }

    if (item.name) {
      return item.name
    }

    const value = String(item.value)

    if (value) {
      const isNumeric = !isNaN(parseInt(value))
      return isNumeric ? value : Translator.translate(value)
    }

    return String(item)
  },
}
