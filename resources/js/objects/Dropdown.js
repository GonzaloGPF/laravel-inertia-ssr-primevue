import lo from 'lodash'
import Utils from '@/objects/Utils'
import Translator from '@/objects/Translator.js'

const DropDown = {
  selectedItems(items, selectedValue, multiple) {
    if (Utils.isEmptyValue(selectedValue)) {
      return multiple ? [] : null
    }
    return multiple
      ? lo.filter(items, (item) =>
          selectedValue
            .map((value) => value.id || value)
            .includes(item.id || item)
        )
      : lo.find(items, { id: selectedValue.id || selectedValue })
  },

  selectionIcon(items, selectedValue, multiple) {
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

  filterAndOrder(items, search, customFilter) {
    return lo
      .chain(items)
      .filter((item) => DropDown.filterItem(item, search, customFilter))
      .orderBy(['label', 'name'])
      .value()
  },

  orderItems(items) {
    return lo.orderBy(items, ['label', 'name'])
  },

  filterItem(item, search, customFilter) {
    if (customFilter) {
      return customFilter(item, search)
    } else {
      return DropDown.searchItem(item, search)
    }
  },

  searchItem(item, search) {
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

  options(values) {
    return values.map((value) => ({
      id: value,
      value,
    }))
  },

  getItemTitle(item) {
    if (item.label) {
      return typeof item.label === 'function' ? item.label() : item.label
    }

    if (item.name) {
      return item.name
    }

    const value = item.value || item

    if (value) {
      const isNumeric = !isNaN(parseInt(value))
      return isNumeric ? value : Translator.translate(value)
    }

    return item
  },
}

export default DropDown
