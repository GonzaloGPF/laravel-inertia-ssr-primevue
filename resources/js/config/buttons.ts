import { ActionName } from '@/types/actions'
import { ButtonProps } from '@/types/buttons'

export const buttons: Record<ActionName, ButtonProps> = {
  send: {
    title: 'send',
    icon: 'send',
  },
  search: {
    title: 'search',
    icon: 'search',
  },
  filter: {
    title: 'filter',
    icon: 'filter_alt',
  },
  view: {
    title: 'view',
    icon: 'visibility',
  },
  create: {
    title: 'create',
    icon: 'add',
  },
  edit: {
    title: 'edit',
    icon: 'edit',
  },
  delete: {
    title: 'delete',
    severity: 'danger',
    icon: 'delete',
  },
  restore: {
    title: 'restore',
    icon: 'restore_from_trash',
  },
  attach: {
    title: 'attach',
    icon: 'link',
  },
  detach: {
    title: 'detach',
    severity: 'danger',
    icon: 'link_off',
  },
  save: {
    title: 'save',
    icon: 'save',
  },
  close: {
    title: 'close',
    icon: 'close',
  },
  cancel: {
    title: 'close',
    icon: 'close',
  },
  download: {
    title: 'download',
    icon: 'download',
  },
  options: {
    title: 'options',
    icon: 'settings',
  },
  select: {
    title: 'select',
    icon: 'touch_app',
  },
  remove: {
    title: 'remove',
    icon: 'block',
  },
  plus: {
    title: 'plus',
    icon: 'add',
  },
  minus: {
    title: 'minus',
    icon: 'remove',
  },
  erase: {
    title: 'erase',
    icon: 'ink_eraser',
  },
  refresh: {
    title: 'refresh',
    icon: 'refresh',
  },
  solve: {
    title: 'solve',
    icon: 'check',
  },
  help: {
    title: 'help',
    icon: 'help',
  },
  web: {
    title: 'web',
    icon: 'language',
  },
  info: {
    title: 'info',
    icon: 'info',
  },
  back: {
    title: 'back',
    icon: 'arrow_back',
  },
  sort: {
    title: 'sort',
    icon: 'sort',
  },
  copy: {
    title: 'copy',
    icon: 'content_copy',
  },
}
