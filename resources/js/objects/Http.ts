import axios, { type AxiosError, type AxiosResponse, type AxiosStatic } from 'axios'
import { app } from '@/config/app'
import { Translator } from '@/objects/Translator'
import EventBus from '@/objects/EventBus'
import { Events } from '@/config/events'
import { FileType } from '@/types/input'
import { ApiErrorResponse } from '@/types/api'

const NO_CONTENT = 204
const CREATED = 201
const ACCEPTED = 202
// const PERMANENTLY_REDIRECT = 308;
const UNAUTHORIZED = 401
// const FORBIDDEN = 403;
// const NOT_FOUND = 404;
const REQUEST_ENTITY_TOO_LARGE = 413
const UNPROCESSABLE_ENTITY = 422

let axiosInstance: AxiosStatic

const Http = {
  getInstance: () => {
    if (axiosInstance) {
      return axiosInstance
    }

    axios.defaults.baseURL = app.getAppURL()
    axios.defaults.withCredentials = true

    axios.interceptors.request.use((config) => {
      const appVersionElement = document.head?.querySelector(
        'meta[name="app_version"]'
      ) as HTMLMetaElement
      config.headers['Accept-Language'] = Translator.getLocale()
      config.headers['X-Requested-With'] = 'XMLHttpRequest'
      config.headers['X-Inertia'] = true
      config.headers['X-Inertia-Version'] = appVersionElement?.content
      // request.headers['X-Socket-Id'] = authStore.sockedId;
      return config
    })

    axios.interceptors.response.use(
      (response) => responseInterceptor(response),
      (error) => errorInterceptor(error, axios)
    )

    axiosInstance = axios

    return axiosInstance
  },

  getJson: (url: string, config = {}) => Http.getInstance().get(url, config),

  putJson: (url: string, data = {}, config = {}) =>
    Http.getInstance().put(url, data, config),

  postJson: (url: string, data = {}, config = {}) =>
    Http.getInstance().post(url, data, config),

  deleteJson: (url: string, config = {}) => Http.getInstance().delete(url, config),

  download: (url: string, params = {}) => {
    return Http.getInstance()
      .get(url, { responseType: 'blob', data: { query: params } })
      .then((response) => {
        const blob = new Blob([response.data], {
          type: response.data.type || 'application/octet-stream',
        })

        const blobURL =
          window.URL && window.URL.createObjectURL
            ? window.URL.createObjectURL(blob)
            : window.webkitURL.createObjectURL(blob)

        const tempLink = document.createElement('a')
        tempLink.style.display = 'none'
        tempLink.href = blobURL
        tempLink.setAttribute('download', response.data.filename)

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
  downloadFile: (file: FileType, params = {}) => {
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

const responseInterceptor = (response: AxiosResponse) => {
  const status = response.status
  const { message } = response.data

  if (!response.config.quietly) {
    if (status === NO_CONTENT) {
      EventBus.emit(Events.flash_message, {
        message: Translator.t('help.no_content'),
        type: 'warning',
      })
    }

    if ([CREATED, ACCEPTED].includes(status)) {
      EventBus.emit(Events.flash_message, {
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

  EventBus.emit(Events.errors, null)

  return response
}

const errorInterceptor = (error: AxiosError<ApiErrorResponse>, axiosInstance: AxiosStatic) => {
  if (!error.response) throw new Error(error.message)

  const { status, data, config } = error.response

  // if (status === PERMANENTLY_REDIRECT) {
  //     authStore.setUser(data.data);
  //     return Promise.resolve(data);
  // }

  // Validation Errors
  if (status === UNPROCESSABLE_ENTITY) {
    EventBus.emit(Events.errors, data.data)
  }

  // Session expired
  if (status === UNAUTHORIZED && data.data?.action === 'reload') {
    return axiosInstance
      .get('csrf-cookie', { quietly: true })
      .then(() => axiosInstance(error.config!)) // retry last request
      .catch(() => window.location.reload())
  }

  if (status >= UNAUTHORIZED) {
    let message = data.message || error.response.statusText

    if (status === REQUEST_ENTITY_TOO_LARGE) {
      message = Translator.t('exceptions.too_large')
    }

    if (config && !config.quietly) {
      EventBus.emit(Events.flash_message, {
        message,
        type: 'error',
      })
    }
  }

  return Promise.reject(error)
}
