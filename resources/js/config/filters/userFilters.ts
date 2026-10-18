import type { FilterField } from '@/types/filter'

export const userFilters: FilterField[] = [
  { name: 'name' },
  { name: 'email' },
  { name: 'phone' },
  { name: 'role', input: 'select', src: 'roles', multiple: true },
  { name: 'language', input: 'select', src: 'languages' },
  { name: 'currency', input: 'select', src: 'currencies' },
  { name: 'verified', input: 'boolean' },
  { name: 'age', input: 'number_range', range: true },
  { name: 'created_at', input: 'date', range: true },
]
