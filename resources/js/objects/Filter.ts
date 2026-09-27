import { Translator } from '@/objects/Translator'
import { InputValue, OptionItem, QueryParams } from '@/types/input'
import type { FilterField } from '@/types/filter'

type FilterInput = {
  input?: string
  value: InputValue | null
  name: string
  label?: string | null
  options?: OptionItem[]
  src?: string
  model?: string
  urlAttribute?: string | number | symbol
  type?: string
  multiple?: boolean
  min?: number | string
  max?: number | string
  range?: boolean
  filter?: string
  params?: QueryParams
}

export const Filter = {
  processFields(
    fields?: FilterField[],
    queryValues: QueryParams = {},
    isInternal = false,
    withTrash = false
  ): FilterInput[] {
    fields = Filter.addCommonFields(fields, isInternal, withTrash)

    const expandedFields: FilterField[] = []
    fields
      .filter(Filter.isVisible)
      .forEach((field) => Filter.addRanges(expandedFields, field))

    return expandedFields.map((field) => Filter.toInput(field, queryValues))
  },

  addCommonFields(
    fields: FilterField[] | null | undefined,
    isInternal: boolean,
    withTrash: boolean
  ): FilterField[] {
    fields = fields || []

    if (!alreadyHas(fields, 'created_at') && isInternal) {
      // fields.push({
      //     name: 'created_at',
      //     type: 'date',
      //     range: true
      // });
    }

    if (!alreadyHas(fields, 'id') && isInternal) {
      fields.push({
        name: 'id',
      })
    }

    if (!alreadyHas(fields, 'deleted') && withTrash) {
      fields.push({
        name: 'deleted',
        type: 'boolean',
      })
    }

    if (!alreadyHas(fields, 'page')) {
      fields.push({
        name: 'page',
        type: 'number',
      })
    }

    return fields
  },

  isVisible(field: FilterField): boolean {
    if ('visible' in field) {
      if (typeof field.visible === 'function') return field.visible()

      return !!field.visible
    }

    return true
  },

  addRanges(fields: FilterField[], field: FilterField): void {
    if (field.range) {
      fields.push({
        ...field,
        name: `min_${field.name}`,
        label: field.label ?? Translator.ta(field.name),
      })
      fields.push({
        ...field,
        name: `max_${field.name}`,
        label: field.label ?? Translator.ta(field.name),
      })
    } else {
      fields.push(field)
    }
  },

  toInput(field: FilterField, queryValues: QueryParams): FilterInput {
    return {
      input: field.input,
      value: queryValues[String(field.name)] ?? null,
      name: String(field.name),
      label: field.label,
      options: field.options,
      src: field.src,
      model: field.model,
      urlAttribute: field.urlAttribute,
      type: field.type,
      multiple: field.multiple,
      min: field.min,
      max: field.max,
      range: field.range,
      filter: field.filter,
      params:
        typeof field.params === 'function' ? field.params() : field.params,
    }
  },
}

function alreadyHas(fields: FilterField[], name: string): boolean {
  return !!fields.find((field) => field.name === name)
}
