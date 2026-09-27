import { ModalProps } from '@/types/modal'
import { MenuItem } from 'primevue/menuitem'
import { ActionName } from '@/types/actions'

export type ButtonProps = {
  title?: string
  action?: ActionName
  icon?: string
  iconPosition?: string
  badge?: string
  badgeSeverity?: string
  loadingIcon?: string
  size?: string
  href?: string
  severity?: string
  label?: string | number | ((action?: string) => string)
  disabled?: boolean
  loading?: boolean
  text?: boolean
  rounded?: boolean
  raised?: boolean
  link?: boolean
  outlined?: boolean
  plain?: boolean
  fluid?: boolean
  modal?: {
    data: ModalProps
    listeners?: object
  }
  menuItems?: MenuItem[]
}

export type ButtonsProps = {
  buttons?: ButtonProps[]
  actions?: ActionName[]
}
