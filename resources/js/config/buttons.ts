import { ActionName } from '@/types/actions'
import { ButtonProps } from '@/types/buttons'

export const buttons: Record<ActionName, ButtonProps> = {
  send: {
    title: 'send',
    icon: 'mdi-send',
  },
  search: {
    title: 'search',
    icon: 'mdi-magnify',
  },
  filter: {
    title: 'filter',
    icon: 'mdi-filter',
  },
  view: {
    title: 'view',
    icon: 'mdi-eye',
  },
  create: {
    title: 'create',
    icon: 'mdi-plus',
  },
  edit: {
    title: 'edit',
    icon: 'mdi-pencil',
  },
  delete: {
    title: 'delete',
    severity: 'danger',
    icon: 'mdi-delete',
  },
  restore: {
    title: 'restore',
    icon: 'mdi-delete-restore',
  },
  attach: {
    title: 'attach',
    icon: 'mdi-link',
  },
  detach: {
    title: 'detach',
    severity: 'danger',
    icon: 'mdi-link-off',
  },
  save: {
    title: 'save',
    icon: 'mdi-content-save',
  },
  close: {
    title: 'close',
    icon: 'mdi-close',
  },
  cancel: {
    title: 'close',
    icon: 'mdi-close',
  },
  download: {
    title: 'download',
    icon: 'mdi-download',
  },
  options: {
    title: 'options',
    icon: 'mdi-cog',
  },
  select: {
    title: 'select',
    icon: 'mdi-hand-pointer',
  },
  remove: {
    title: 'remove',
    icon: 'mdi-cancel',
  },
  plus: {
    title: 'plus',
    icon: 'mdi-plus',
  },
  minus: {
    title: 'minus',
    icon: 'mdi-minus',
  },
  erase: {
    title: 'erase',
    icon: 'mdi-eraser',
  },
  refresh: {
    title: 'refresh',
    icon: 'mdi-refresh',
  },
  solve: {
    title: 'solve',
    icon: 'mdi-check',
  },
  help: {
    title: 'help',
    icon: '$help',
  },
  web: {
    title: 'web',
    icon: 'mdi-web',
  },
  info: {
    title: 'info',
    icon: 'mdi-information',
  },
  back: {
    title: 'back',
    icon: 'mdi-arrow-left',
  },
  sort: {
    title: 'sort',
    icon: 'mdi-sort',
  },
  copy: {
    title: 'copy',
    icon: 'mdi-content-copy',
  },
}
