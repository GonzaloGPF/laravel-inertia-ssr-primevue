import 'axios'

declare module 'axios' {
  export interface AxiosRequestConfig {
    quietly?: boolean
  }
}
