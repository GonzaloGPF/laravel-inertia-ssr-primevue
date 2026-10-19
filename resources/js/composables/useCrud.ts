import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Formatter } from '@/objects/Formatter'
import { useConfirm } from '@/stores/confirm'
import { useFlashMessages } from '@/stores/flashMessages'
import { Translator } from '@/objects/Translator'
import { ActionName } from '@/types/actions'

type TableInstance = { refresh: () => void }

type Options<T> = {
  /** Returns the reason why a row can't be deleted, if there is any. */
  deleteGuard?: (row: T) => string | undefined
}

/**
 * Common logic of the admin views: an `AppDataTable` of models, a modal to
 * create or edit them, the page of their details and the confirmation to delete them. `table` must be
 * bound to the table with `ref="table"`.
 *
 * The modal edits the `item` of the row whose `edit` action was clicked, or
 * creates a new one when there is no `item`.
 *
 * The table `src` is not resolved here, as `route()` is only globally
 * available in the browser, so each view gives it to the table in its template.
 */
export default function useCrud<T extends { id?: number | string }>(
  modelName: string,
  options: Options<T> = {}
) {
  const { showConfirm } = useConfirm()
  const { pushFlashMessage } = useFlashMessages()
  const resource = Formatter.plural(Formatter.snakeCase(modelName))
  const table = ref<TableInstance | null>(null)
  const showModal = ref(false)
  const item = ref<T | null>(null)
  const deleting = ref(false)

  const title = computed(() => Translator.modelTitle(modelName, true))
  const modalTitle = computed(() =>
    Translator.actionTitle(item.value ? 'edit' : 'create', modelName)
  )

  const onDelete = async (row: T) => {
    if (deleting.value) {
      return
    }

    const reason = options.deleteGuard?.(row)

    if (reason) {
      pushFlashMessage({ type: 'error', message: reason })
      return
    }

    const confirmed = await showConfirm(Translator.t('help.delete_text'), {
      okText: Translator.actionTitle('delete'),
    })

    if (!confirmed) {
      return
    }

    router.delete(route(`${resource}.destroy`, row.id), {
      preserveState: true,
      preserveScroll: true,
      onStart: () => (deleting.value = true),
      onSuccess: () => table.value?.refresh(),
      onFinish: () => (deleting.value = false),
    })
  }

  const onAction = (action: ActionName, row?: T) => {
    if (action === 'create') {
      item.value = null
      showModal.value = true
    }

    if (action === 'edit' && row) {
      item.value = row
      showModal.value = true
    }

    if (action === 'view' && row) {
      router.visit(route(`${resource}.show`, row.id))
    }

    if (action === 'delete' && row) {
      void onDelete(row)
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
