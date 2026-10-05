import AppChip from '@/Components/Core/AppChip.vue'
import { Translator } from '@/objects/Translator'
import { User } from '@/types'
import { TableColumn } from '@/types/table'

export const userColumns: TableColumn<User>[] = [
  { field: 'name' },
  { field: 'email' },
  {
    field: 'role',
    component: AppChip,
    props: (user) => ({ label: Translator.tConstName('roles', user.role) }),
  },
  {
    field: 'language',
    component: AppChip,
    props: (user) => ({
      label: Translator.tConstName('languages', user.language),
    }),
  },
]
