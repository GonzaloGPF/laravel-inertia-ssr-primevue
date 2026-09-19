import { computed, onMounted, ref, toRefs } from 'vue'
import useHttp from '@/composables/useHttp'
import { Translator } from '@/objects/Translator'
import { useConfirm } from '@/stores/confirm'
import { Formatter } from '@/objects/Formatter'
import { router, usePage } from '@inertiajs/vue3'
import { PaginationData } from '@/types/api'
import { BaseModel, WithFile } from '@/types/base-model'

export default <T extends BaseModel = BaseModel>(modelName: string) => {
  const { props } = toRefs(usePage())
  const { showConfirm } = useConfirm()
  const { downloadFile, loading } = useHttp()
  const paginatedData = props.value?.data as PaginationData
  const total = Number(paginatedData.total || 0)
  const perPage = Number(paginatedData.per_page || 25)
  const baseUrl = Formatter.plural(Formatter.snakeCase(modelName))

  const currentPage = ref(paginatedData.current_page || 1)
  const totalPages = ref(Math.ceil(total / perPage))

  const title = computed(
    () => `${Translator.modelTitle(modelName, true)} [${paginatedData.total}]`
  )
  const items = computed(() => paginatedData?.data || [])

  function onCreate() {
    router.visit(`${baseUrl}/create`)
  }

  function onEdit(item: T) {
    router.visit(`${baseUrl}/${item.id}/edit`)
  }

  function onDownload(item: WithFile) {
    downloadFile(item.file)
  }

  function onView(item: T) {
    return router.visit(`${baseUrl}/${item.id}`)
  }

  async function onDelete(item: T) {
    if (loading.value) {
      return
    }

    // if (item.deleted_at) return onRestore(item) // TODO: ask about force delete

    if (!(await showConfirm(Translator.t('help.delete_text')))) {
      return
    }

    return router.delete(`${baseUrl}/${item.id}`)
  }

  function onImpersonate(item: T) {
    router.visit(`${baseUrl}/impersonate/take/${item.id}`)
  }

  function refresh() {
    router.reload({ only: ['data'] })
  }

  onMounted(() => refresh())

  return {
    title,
    loading,
    items,
    currentPage,
    totalPages,
    perPage,
    refresh,
    onView,
    onCreate,
    onEdit,
    onDelete,
    onImpersonate,
    onDownload,
  }
}
