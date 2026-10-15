import {
  computed,
  onMounted,
  ref,
  toValue,
  watch,
  type MaybeRefOrGetter,
} from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import useHttp from '@/composables/useHttp'
import { QueryString } from '@/objects/QueryString'
import { PaginationData } from '@/types/api'

const PAGE = 'page'
const DEFAULT_PER_PAGE = 25
const PER_PAGE = 'perPage' // Query string name, the backend expects `per_page`

/**
 * Loads the items of a table from a json `src`. The pagination lives in the
 * url (`?page=2&perPage=25`), so the url is the single source of truth: when it
 * changes (a page click, or browser back/forward) the items are requested again.
 */
export default function useDataTable<T extends object>(
  src: MaybeRefOrGetter<string>
) {
  const page = usePage()
  const { getJson, loading } = useHttp()

  const items = ref<T[]>([])
  const total = ref(0)
  const responsePerPage = ref<number>()
  const path = computed(() => page.url.split('?')[0])
  const query = computed(() => QueryString.parseSearch(page.url.split('?')[1]))

  const currentPage = computed(() => Number(query.value[PAGE]) || 1)
  const perPage = computed(
    () =>
      Number(query.value[PER_PAGE]) || responsePerPage.value || DEFAULT_PER_PAGE
  )

  let lastRequest = 0
  let mountedPath = ''

  const fetchItems = async () => {
    const request = ++lastRequest

    // On failure, Http already notifies the error to the user
    const response = await getJson(toValue(src), {
      params: requestParams(),
      headers: partialReloadHeaders(),
    }).catch(() => null)

    if (!response || request !== lastRequest) {
      return // Failed, or a newer request is already in charge
    }

    const { data } = response
    const paginated = (data.props?.data ?? data) as PaginationData<T[]>
    items.value = paginated.data ?? []
    total.value = paginated.total ?? 0
    responsePerPage.value = Number(paginated.per_page) || undefined

    // The current page no longer exists, i.e. its last item was deleted
    const lastPage = paginated.last_page ?? 1

    if (!items.value.length && currentPage.value > lastPage) {
      goTo(lastPage, perPage.value, true)
    }
  }

  const requestParams = () => {
    const { [PER_PAGE]: perPage, ...params } = query.value

    return perPage === undefined ? params : { ...params, per_page: perPage }
  }

  // Asks an Inertia page only for its `data` prop. Plain json endpoints ignore it.
  const partialReloadHeaders = () => ({
    'X-Inertia-Partial-Component': page.component,
    'X-Inertia-Partial-Data': 'data',
  })

  const goTo = (pageNumber: number, rows: number, replace = false) => {
    const search = QueryString.toSearch({
      ...query.value,
      [PAGE]: String(pageNumber),
      [PER_PAGE]: String(rows),
    })

    const visitOptions = {
      url: `${path.value}?${search}`,
      preserveState: true,
      preserveScroll: true,
    }

    if (replace) {
      router.replace(visitOptions)
    } else {
      router.push(visitOptions)
    }
  }

  onMounted(() => {
    mountedPath = path.value
    void fetchItems()
  })

  watch(
    () => page.url,
    () => {
      // Ignore navigation to other pages, this component is about to leave
      if (path.value === mountedPath) {
        void fetchItems()
      }
    }
  )

  watch(
    () => toValue(src),
    () => void fetchItems()
  )

  return {
    items,
    total,
    loading,
    currentPage,
    perPage,
    goTo,
    refresh: fetchItems,
  }
}
