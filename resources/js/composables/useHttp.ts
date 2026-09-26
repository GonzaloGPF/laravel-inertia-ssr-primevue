import { ref, unref } from 'vue'
import Http from '@/objects/Http'
import { FileType } from '@/types/input'

export default () => {
  const loading = ref(false)

  const getJson = (url: string, config = {}) => {
    return execute(Http.getJson(url, config))
  }

  const putJson = (url: string, data = {}, config = {}) => {
    return execute(Http.putJson(url, unref(data), config))
  }

  const postJson = (url: string, data = {}, config = {}) => {
    return execute(Http.postJson(url, unref(data), config))
  }

  const deleteJson = (url: string, config = {}) => {
    return execute(Http.deleteJson(url, config))
  }

  const download = (url: string, params = {}) => Http.download(url, params)

  const downloadFile = (file?: FileType, params = {}) => {
    if(!file) return
    Http.downloadFile(file, params)
  }

  const downloadZip = (fileIds = []) => Http.downloadZip(fileIds)

  const execute = async <T>(promise: Promise<T>) => {
    loading.value = true

    try {
      return await promise
    } finally {
      loading.value = false
    }
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
