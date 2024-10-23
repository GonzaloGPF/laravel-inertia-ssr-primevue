import { ref, unref } from 'vue'
import Http from '@/objects/Http'

export default () => {
  const loading = ref(false)
  /**
   * Basic GET request
   *
   * @param {string} url
   * @param {null|{}} config
   * @returns {Promise<T>}
   */
  const getJson = (url, config = {}) => execute(Http.getJson(url, config))

  /**
   * Basic PUT request
   *
   * @param {string} url
   * @param {*} data
   * @param {null|{}} config
   * @returns {Promise}
   */
  const putJson = (url, data = {}, config = null) => {
    return execute(Http.putJson(url, unref(data), config))
  }

  /**
   * Basic POST request
   *
   * @param {string} url
   * @param {*} data
   * @param {null|{}} config
   * @returns {Promise}
   */
  const postJson = (url, data = {}, config = null) => {
    return execute(Http.postJson(url, unref(data), config))
  }

  /**
   * Basic DELETE request
   *
   * @param {string} url
   * @param {null|{}} config
   * @returns {Promise}
   */
  const deleteJson = (url, config = null) => {
    return execute(Http.deleteJson(url, config))
  }

  /**
   * Open a new tab with specified url
   *
   * @param {string} url
   * @param {*} params
   */
  const download = (url, params = {}) => Http.download(url, params)

  /**
   *
   * @param file
   * @param {*|null} params
   * @returns {Promise<AxiosResponse<any>>}
   */
  const downloadFile = (file, params = {}) => Http.downloadFile(file, params)

  /**
   * @param fileIds
   * @returns {Promise<AxiosResponse<*>>}
   */
  const downloadZip = (fileIds = []) => Http.downloadZip(fileIds)

  /**
   * Executes the given Promise
   *
   * @param {Promise} promise
   * @returns {Promise}
   */
  const execute = (promise) => {
    loading.value = true

    return promise.finally(() => {
      loading.value = false
    })
  }

  return {
    loading,
    getJson,
    putJson,
    postJson,
    deleteJson,
    download,
    downloadFile,
    downloadZip,
  }
}
