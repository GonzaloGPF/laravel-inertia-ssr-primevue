import lodash from 'lodash'
import { Time } from '@/objects/Time'
import { app } from '@/config/app'
import { QueryString } from '@/objects/QueryString'
import type { InputValue, QueryParams } from '@/types/input.d.ts'
import { BaseModel } from '@/types/base-model'

export const Utils = {
  /**
   * Deep copy of an Object. You can pass an excepted array to remove desired keys
   */
  copy<T>(obj: T, except: Array<keyof T> = []): T {
    const result = { ...obj }

    except.forEach((key) => delete result[key])

    return JSON.parse(JSON.stringify(result)) as T // This destroys File variables
  },

  /**
   * Returns the given object, removing all keys with empty values
   */
  removeEmpty<T>(
    obj?: T
  ): Partial<T> | Record<string, never> {
    if (!obj) return {}

    const cloned = JSON.parse(JSON.stringify(obj)) as Record<string, unknown>

    Object.keys(cloned).forEach((k) => {
      const value = cloned[k]

      if (value && typeof value === 'object') {
        cloned[k] = Utils.removeEmpty(value as BaseModel)
        return
      }

      if (Utils.isEmptyValue(value as InputValue)) {
        delete cloned[k]
      }
    })

    return cloned as Partial<T>
  },

  /**
   * Checks if a given value is empty
   */
  isEmptyValue(value?: InputValue | null): boolean {
    if (Array.isArray(value)) return value.length === 0

    return value === '' || value === null || value === undefined
  },

  isEquals<T>(obj1?: T, obj2?: T): boolean {
    return lodash.isEqual(Utils.removeEmpty(obj1), Utils.removeEmpty(obj2))
  },

  /**
   * Returns a file URL, or the default logo if no file is provided
   */
  getFileURL(file?: BaseModel, cacheBreaking = false): string {
    if (!file) {
      return app.getAppURL('/images/logo.svg')
    }

    const params: QueryParams & { cache?: number } = {}

    if (cacheBreaking) {
      params.cache = Time.now().getTime()
    }

    const query = QueryString.stringify(params)

    const url = app.getAppURL(`/api/files/${file.id}`)

    if (query.length) {
      return `${url}?${query}`
    }

    return url
  },

  /**
   * Opens the given path in a new tab
   */
  openTab(path?: string): Promise<void> | void {
    if (!path) {
      return
    }

    let url: string

    if (path.startsWith('http')) {
      url = path
    } else {
      url = app.getAppURL(path)
    }

    const tab = window.open(url, '_blank')

    if (!tab) {
      return
    }

    tab.focus()

    return new Promise((resolve) => {
      tab.addEventListener('beforeunload', () => resolve())
    })
  },

  isSamePath(path: string): boolean {
    return new URL(path).pathname === window.location.pathname
  },

  getBasePath(): string {
    const url = new URL(String(document.location)).pathname.split('/')[1]

    return `/${url}`
  },

  goBack(): void {
    window.history.back()
  },
}
