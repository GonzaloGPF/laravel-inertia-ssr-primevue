import { BaseModel } from '@/types/base-model'

type PaginationLink = {
  url?: string
  label?: string
  active?: boolean
}

type PaginationData<T = BaseModel> = {
  data?: T,
  current_page?: number
  first_page_url?: string
  from?: number
  last_page?: number
  last_page_url?: string
  links?: PaginationLink[]
  next_page_url?: string
  path?: string
  per_page?: string
  prev_page_url?: string
  to?: number
  total?: number
}

export type ApiResponse<T> = {
  data?: T,
  message?: string
}

export type ApiErrorResponse = {
  message?: string
  data?: {
    message?: string
    action?: string
    data?: any
  }
}