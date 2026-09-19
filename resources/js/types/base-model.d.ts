import { FileType } from '@/types/input'

export type BaseModel = {
  id?: number | string
  name?: string
  label?: string
  created_by: number | string
  created_at?: string | Date
  updated_at?: string | Date
  deleted_at?: string | Date
}

export type WithFile = BaseModel & {
  file?: FileType
}