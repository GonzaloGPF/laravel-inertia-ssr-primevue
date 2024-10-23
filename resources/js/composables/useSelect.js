import { computed, ref, unref } from 'vue'
import Dropdown from '@/objects/Dropdown.js'
import useConstants from '@/composables/useConstants.js'

/**
 *
 * @param value
 * @param {string} src
 * @param multiple
 * @param {?ref<string>} search
 * @param {?function} customFilter
 * @param {?ref<Array>} customOptions
 * @returns {{getItemTitle: (function(*): (*)), selectionIcon: ComputedRef<string>, items: ComputedRef<*|T[]>, selectedItems: ComputedRef<unknown>}}
 */
export default (value, src, multiple = false, search = null, customFilter, customOptions) => {
    search = ref(search)
    customOptions = ref(customOptions)

    const items = computed(() => {
        const items = customOptions?.value || useConstants().getConstants(src)

        return Dropdown.filterAndOrder(items, search.value, customFilter)
    })

    const selectedItems = computed(() => Dropdown.selectedItems(items.value, unref(value), unref(multiple)))

    const selectionIcon = computed(() => Dropdown.selectionIcon(items.value, unref(value), unref(multiple)))

    const getItemTitle = (item) => Dropdown.getItemTitle(item)

    return {
        items,
        selectedItems,
        selectionIcon,
        getItemTitle,
    }
}
