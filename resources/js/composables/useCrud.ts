import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Formatter } from '@/objects/Formatter'
import { useConfirm } from '@/stores/confirm'
import { useFlashMessages } from '@/stores/flashMessages'
import { Translator } from '@/objects/Translator'
import { ActionName } from '@/types/actions'

type TableInstance = {
  refresh: () => void
  removeRow: (id: number | string) => Promise<void>
}

type Options<T> = {
  /** Returns the reason why a row can't be deleted, if there is any. */
  deleteGuard?: (row: T) => string | undefined
}

/**
 * Common logic of the admin views: an `AppDataTable` of models, a modal to
 * create or edit them, the page of their details, the confirmation to delete them (and to delete them
 * for good) and the restoring of the deleted ones. `table` must be
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
  const sending = ref(false)

  const title = computed(() => Translator.modelTitle(modelName, true))
  const modalTitle = computed(() =>
    Translator.actionTitle(item.value ? 'edit' : 'create', modelName)
  )

  /**
   * Sends the request of an action on the row. The row leaves the table when it is done: the
   * list the table shows is not the one it belongs to any more.
   */
  const send = (method: 'put' | 'delete', routeName: string, row: T) =>
    router.visit(route(`${resource}.${routeName}`, row.id), {
      method,
      preserveState: true,
      preserveScroll: true,
      onStart: () => (sending.value = true),
      onSuccess: () => table.value?.removeRow(row.id as number | string),
      onFinish: () => (sending.value = false),
    })

  const onDelete = async (row: T) => {
    if (sending.value) {
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

    if (confirmed) {
      send('delete', 'destroy', row)
    }
  }

  const onForceDelete = async (row: T) => {
    if (sending.value) {
      return
    }

    const confirmed = await showConfirm(
      Translator.t('help.force_delete_text'),
      { okText: Translator.actionTitle('force_delete') }
    )

    if (confirmed) {
      send('delete', 'force-delete', row)
    }
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

    if (action === 'restore' && row && !sending.value) {
      send('put', 'restore', row)
    }

    if (action === 'force_delete' && row) {
      void onForceDelete(row)
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
