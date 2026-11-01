import AppAvatar from '@/Components/Core/AppAvatar.vue'
import AppChip from '@/Components/Core/AppChip.vue'
import { Translator } from '@/objects/Translator'
import { User } from '@/types'
import { TableColumn } from '@/types/table'

export const userColumns: TableColumn<User>[] = [
  {
    field: 'name',
    component: AppAvatar,
    props: (user) => ({
      src: user.avatar_url,
      name: user.name,
      showName: true,
    }),
  },
  { field: 'email' },
  { field: 'age' },
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
