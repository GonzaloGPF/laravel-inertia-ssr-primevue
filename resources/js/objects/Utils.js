import lodash from 'lodash'
import Time from '@/objects/Time'
import app from '@/config/app.js'
import QueryString from '@/objects/QueryString.js'

const Utils = {
  // /**
  //  * Transforms given values in an array of objects with id, value and label
  //  * @param {array} values
  //  * @param {function|null} translator
  //  * @returns {array}
  //  */
  // options: (values, translator = null) => {
  //     if (! Array.isArray(values)) return [];
  //
  //     return values.map((value) => ({
  //         id: value,
  //         value: value,
  //         label: translator
  //             ? translator(value)
  //             : Translator.translate(value),
  //     }))
  // },

  /**
   * Deep copy of an Object. You can pass an except array to remove desired keys
   *
   * @param obj
   * @param except
   * @returns {any}
   */
  copy: (obj, except = []) => {
    const result = { ...obj }

    except.forEach((key) => delete result[key])

    return JSON.parse(JSON.stringify(result)) // This destroys File variables
  },

  /**
   * Returns the given object, removing all keys with empty values
   * @param obj
   * @returns {{}|*}
   */
  removeEmpty: (obj) => {
    if (!obj) return {}

    obj = JSON.parse(JSON.stringify(obj)) // deep clone

    Object.keys(obj).forEach(
      (k) =>
        (obj[k] && typeof obj[k] === 'object' && Utils.removeEmpty(obj[k])) ||
        (Utils.isEmptyValue(obj[k]) && delete obj[k])
    )

    return obj
  },

  /**
   * Checks if a given value is empty
   * @param value
   * @returns {boolean}
   */
  isEmptyValue: (value) => {
    if (Array.isArray(value)) return value.length === 0

    return value === '' || value === null || value === undefined
  },

  /**
   *
   * @param obj1
   * @param obj2
   * @returns {boolean}
   */
  areEquals: (obj1, obj2) => {
    return lodash.isEqual(Utils.removeEmpty(obj1), Utils.removeEmpty(obj2))
  },

  /**
   *
   * @param file
   * @param {boolean} cacheBreaking
   * @returns {string|null}
   */
  getFileURL: (file, cacheBreaking = false) => {
    if (!file) return app.getApplUrl('/images/logo.svg')

    const params = {}

    // const { user } = useAuth();
    //
    // if (user && user.locale) {
    //     params.locale = user.locale;
    // }

    if (cacheBreaking) {
      params.cache = Time.now().getTime()
    }

    const query = QueryString.stringify(params)

    const url = app.getApplUr(`/api/files/${file.id}`)

    if (query.length) {
      return `${url}?${query}`
    }

    return url
  },

  /**
   *
   * @param path
   * @returns {Promise<unknown>|boolean}
   */
  openTab: (path) => {
    if (!path) {
      return false
    }

    let url = null

    if (path.startsWith('http')) {
      url = path
    } else {
      url = app.getApplUrl(path)
    }

    const tab = window.open(url, '_blank')

    tab.focus()

    return new Promise((resolve) => {
      tab.addEventListener('beforeunload', () => resolve())
    })
  },

  isSamePath: (path) => {
    return new URL(path).pathname === window.location.pathname
  },

  getBasePath: () => {
    const url = new URL(document.location).pathname.split('/')[1]

    return `/${url}`
  },

  goBack: () => window.history.back(),
}
export default Utils
