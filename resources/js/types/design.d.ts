import { severities } from '@/config/design'

export type Size = 'x-small' | 'small' | 'medium' | 'large' | 'x-large'
export type Severity = (typeof severities)[number]
