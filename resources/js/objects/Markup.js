import Translator from '@/objects/Translator'
import Formatter from '@/objects/Formatter'

export default {
  badge: (constant, name) => {
    const label = Translator.tConstName(constant, name)

    return buildBadgeHtml(label)
  },

  role: (name) => buildBadge('role', name),

  levels: (values) =>
    values ? values.map((value) => buildBadge('level', value)) : [],

  datetime: (value) => {
    const formattedDate = Formatter.dateTimeFormat(value) ?? ''

    let datePieces = formattedDate.split(' ')

    if (datePieces.length !== 2) {
      datePieces = ['-', null]
    }

    return buildTwoValues(datePieces[0], datePieces[1], true)
  },

  amountAndPercent: (value, percent) => {
    const formattedMoney = Formatter.money(value)

    const formattedPercent = Formatter.percent(percent)

    return buildTwoValues(
      formattedMoney,
      percent ? `(${formattedPercent})` : null,
      true,
      true
    )
  },
}

function buildTwoValues(firstValue, secondValue, stacked, centered) {
  const displayClass = stacked ? 'block' : 'inline-block'

  let element = `<span class="${displayClass} whitespace-nowrap">${firstValue}</span>`

  if (secondValue) {
    element += `<span class="${displayClass} text-caption font-weight-light whitespace-nowrap">${secondValue}</span>`
  }

  const containerClass = centered ? 'text-center' : 'text-start'

  return `<div class="${containerClass}">${element}</div>`
}

/**
 * @param {string} constant
 * @param {string} name
 * @returns {string}
 */
function buildBadge(constant, name) {
  if (!name) return '-'

  const label = Translator.tConstName(constant, name)

  constant = Formatter.snakeCase(constant)
  name = Formatter.snakeCase(name)

  const bgColor = `bg-${constant}_${name}` // Colors defined in tailwind.config.ts

  return buildBadgeHtml(label, bgColor)
}

function buildBadgeHtml(label, color) {
  if (!color) {
    color = 'bg-grey-darken-2 text-white'
  }
  return `<span class="whitespace-nowrap py px-2 rounded-lg ${color}">${label}</span>`
}
