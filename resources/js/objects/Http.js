import axios from 'axios'
import app from '@/config/app.js'
import Translator from '@/objects/Translator.js'
import QueryString from '@/objects/QueryString.js'
import EventBus from './EventBus.js'
import events from '../config/events.js'

const NO_CONTENT = 204
const CREATED = 201
const ACCEPTED = 202
// const PERMANENTLY_REDIRECT = 308;
const UNAUTHORIZED = 401
// const FORBIDDEN = 403;
// const NOT_FOUND = 404;
const REQUEST_ENTITY_TOO_LARGE = 413
const UNPROCESSABLE_ENTITY = 422

let axiosInstance

const Http = {
  getInstance: () => {
    if (axiosInstance) {
      return axiosInstance
    }

    axios.defaults.baseURL = app.getAppURL()
    axios.defaults.withCredentials = true

    axios.interceptors.request.use((config) => {
      config.headers['Accept-Language'] = Translator.getLocale()
      config.headers['X-Requested-With'] = 'XMLHttpRequest'
      config.headers['X-Inertia'] = true
      config.headers['X-Inertia-Version'] = document.head.querySelector(
        'meta[name="app_version"]'
      ).content
      // request.headers['X-Socket-Id'] = authStore.sockedId;
      return config
    })

    axios.interceptors.response.use(
      (response) => responseInterceptor(response),
      (error) => errorInterceptor(error, axios)
    )

    return (axiosInstance = axios)
  },
  /**
   * Basic GET request
   *
   * @param {string} url
   * @param {null|{}} config
   * @returns {Promise<T>}
   */
  getJson: (url, config = {}) => Http.getInstance().get(url, config),

  /**
   * Basic PUT request
   *
   * @param {string} url
   * @param {*} data
   * @param {null|{}} config
   * @returns {Promise}
   */
  putJson: (url, data = {}, config = null) =>
    Http.getInstance().put(url, data, config),

  /**
   * Basic POST request
   *
   * @param {string} url
   * @param {*} data
   * @param {null|{}} config
   * @returns {Promise}
   */
  postJson: (url, data = {}, config = null) =>
    Http.getInstance().post(url, data, config),

  /**
   * Basic DELETE request
   *
   * @param {string} url
   * @param {null|{}} config
   * @returns {Promise}
   */
  deleteJson: (url, config = null) => Http.getInstance().delete(url, config),

  /**
   * Open a new tab with specified url
   *
   * @param {string} url
   * @param {*} params
   */
  download: (url, params = {}) => {
    const query = QueryString.stringify(params)

    return Http.getInstance()
      .get(`${url}?${query}`, { responseType: 'blob' })
      .then((response) => {
        const blob = new Blob([response], {
          type: response.type || 'application/octet-stream',
        })

        const blobURL =
          window.URL && window.URL.createObjectURL
            ? window.URL.createObjectURL(blob)
            : window.webkitURL.createObjectURL(blob)

        const tempLink = document.createElement('a')
        tempLink.style.display = 'none'
        tempLink.href = blobURL
        tempLink.setAttribute('download', response.filename)

        document.body.appendChild(tempLink)

        tempLink.click()

        // Fixes "webkit blob resource error 1"
        setTimeout(() => {
          document.body.removeChild(tempLink)
          window.URL.revokeObjectURL(blobURL)
        }, 200)
      })
  },

  /**
   *
   * @param file
   * @param {*|null} params
   */
  downloadFile: (file, params = {}) => {
    if (!file) {
      return
    }

    if (typeof file === 'number') {
      return Http.download(`/files/${file}`, params)
    }

    return Http.download(`/files/${file.id}`, params)
  },

  /**
   * @param fileIds
   */
  downloadZip: (fileIds = []) => {
    if (!Array.isArray(fileIds)) {
      return
    }

    return Http.download('/files/multiple', { file_ids: fileIds })
  },
}

export default Http

const responseInterceptor = (response) => {
  const status = response.status
  const { message } = response.data

  if (!response.config.quietly) {
    if (status === NO_CONTENT) {
      EventBus.emit(events.flash_message, {
        message: Translator.t('help.no_content'),
        type: 'warning',
      })
    }

    if ([CREATED, ACCEPTED].includes(status)) {
      EventBus.emit(events.flash_message, {
        message,
        type: 'success',
      })
    }
  }

  const content = response?.headers?.['content-disposition']
  if (content) {
    response.data.filename = content.replace(
      'attachment; filename=',
      '',
      content
    )
  }

  EventBus.emit(events.errors, null)

  return response
}

const errorInterceptor = (error, axiosInstance) => {
  if (!error.response) throw new Error(error)

  const { status, data, config } = error.response

  // if (status === PERMANENTLY_REDIRECT) {
  //     authStore.setUser(data.data);
  //     return Promise.resolve(data);
  // }

  // Validation Errors
  if (status === UNPROCESSABLE_ENTITY) {
    EventBus.emit(events.errors, data.data)
  }

  // Session expired
  if (status === UNAUTHORIZED && data.data?.action === 'reload') {
    return axiosInstance
      .get('csrf-cookie', { quietly: true })
      .then(() => axiosInstance(error.config)) // retry last request
      .catch(() => window.location.reload())
  }

  if (status >= UNAUTHORIZED) {
    let message = data.message || error.response.statusText

    if (status === REQUEST_ENTITY_TOO_LARGE) {
      message = Translator.t('exceptions.too_large')
    }

    if (config && !config.quietly) {
      EventBus.emit(events.flash_message, {
        message,
        type: 'error',
      })
    }
  }

  return Promise.reject(error)
}
