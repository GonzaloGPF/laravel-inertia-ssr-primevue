import { computed, ref } from 'vue'
import { Translator } from '@/objects/Translator'
import { ActionName } from '@/types/actions'

type TableInstance = { refresh: () => void }

/**
 * Common logic of the admin views: an `AppDataTable` of models, and a modal
 * to create or edit them. `table` must be bound to the table with `ref="table"`.
 *
 * The modal edits the `item` of the row whose `edit` action was clicked, or
 * creates a new one when there is no `item`.
 *
 * The table `src` is not resolved here, as `route()` is only globally
 * available in the browser, so each view gives it to the table in its template.
 */
export default function useCrud<T extends object>(modelName: string) {
  const table = ref<TableInstance | null>(null)
  const showModal = ref(false)
  const item = ref<T | null>(null)

  const title = computed(() => Translator.modelTitle(modelName, true))
  const modalTitle = computed(() =>
    Translator.actionTitle(item.value ? 'edit' : 'create', modelName)
  )

  const onAction = (action: ActionName, row?: T) => {
    if (action === 'create') {
      item.value = null
      showModal.value = true
    }

    if (action === 'edit' && row) {
      item.value = row
      showModal.value = true
    }
  }

  const onSaved = () => {
    showModal.value = false
    table.value?.refresh()
  }

  return {
    table,
    item,
    title,
    modalTitle,
    showModal,
    onAction,
    onSaved,
  }
}
