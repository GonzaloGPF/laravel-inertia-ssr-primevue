import type { Component } from 'vue'
import { ActionName } from '@/types/actions'

type CellProps = Record<string, unknown>

export type TableColumn<T extends object = object> = {
  /** Item attribute shown in the column. */
  field: string
  /** Translation key of the header. Defaults to the `field`. */
  header?: string
  /** Component rendered inside the cell instead of the plain value. */
  component?: Component
  /**
   * Props given to `component`. By default, the component receives
   * the cell's value as `value`.
   */
  props?: CellProps | ((item: T) => CellProps)
}

export type TableActions<T extends object = object> =
  ActionName[] | ((item: T) => ActionName[])
