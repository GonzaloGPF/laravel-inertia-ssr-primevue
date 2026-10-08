import { computed, ref } from 'vue'
import { Translator } from '@/objects/Translator'
import { ActionName } from '@/types/actions'

type TableInstance = { refresh: () => void }

/**
 * Common logic of the admin views: an `AppDataTable` of models, and a modal
 * to create them. `table` must be bound to the table with `ref="table"`.
 *
 * The table `src` is not resolved here, as `route()` is only globally
 * available in the browser, so each view gives it to the table in its template.
 */
export default function useCrud(modelName: string) {
  const table = ref<TableInstance | null>(null)
  const showCreateModal = ref(false)

  const title = computed(() => Translator.modelTitle(modelName, true))
  const createTitle = computed(() =>
    Translator.actionTitle('create', modelName)
  )

  const onAction = (action: ActionName) => {
    if (action === 'create') {
      showCreateModal.value = true
    }
  }

  const onCreated = () => {
    showCreateModal.value = false
    table.value?.refresh()
  }

  return {
    table,
    title,
    createTitle,
    showCreateModal,
    onAction,
    onCreated,
  }
}
