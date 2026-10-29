import { Config } from 'ziggy-js'

export interface User {
  id: string
  name?: string
  email?: string
  email_verified_at?: string
  role?: string
  phone?: string
  age?: number | null
  password?: string
  currency?: string
  language?: string
  avatar_url?: string | null
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  auth: {
    user: User
  }
  ziggy: Config & { location: string }
}
