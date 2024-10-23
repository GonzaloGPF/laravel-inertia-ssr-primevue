import lodash from 'lodash'
import Translator from '@/objects/Translator'

const Filter = {
  processFields(fields, queryValues, isInternal, withTrash) {
    fields = Filter.addCommonFields(fields, isInternal, withTrash)

    return lodash
      .chain(fields)
      .filter(Filter.isVisible)
      .transform(Filter.addRanges)
      .map((field) => Filter.toInput(field, queryValues))
      .value()
  },

  addCommonFields(fields, isInternal, withTrash) {
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

  isVisible(field) {
    if ('visible' in field) {
      if (typeof field.visible === 'function') return field.visible()

      return field.visible
    }

    return true
  },

  addRanges(fields, field) {
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

  toInput(field, queryValues) {
    return {
      input: field.input,
      value: queryValues[field.name] || null,
      name: field.name,
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

function alreadyHas(fields, name) {
  return !!fields.find((field) => field.name === name)
}

export default Filter
