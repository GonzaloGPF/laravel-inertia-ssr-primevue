import {
  getActiveLanguage,
  I18n,
  loadLanguageAsync,
  trans,
  transChoice,
} from 'laravel-vue-i18n'
import Formatter from '@/objects/Formatter'
import { es } from 'date-fns/locale'

const Translator = {
  getLocale: () => getActiveLanguage(),

  getDateLocale() {
    const locales = {
      es,
    }

    return locales[Translator.getLocale()]
  },

  setLocale(locale) {
    if (!locale) return

    document.documentElement.setAttribute('lang', locale)

    return loadLanguageAsync(locale)
  },

  t: (string, options = {}) => trans(string, options),

  /**
   * Translates an attribute
   *
   * @param {string} attribute
   * @returns {string}
   */
  ta: (attribute) => {
    if (!attribute) return ''

    return Formatter.title(Translator.t(`validation.attributes.${attribute}`))
  },

  /**
   * Translates a label
   *
   * @param {string} label
   * @param {*} options
   * @returns {string}
   */
  tl: (label, options = {}) => {
    if (!label) return ''

    return Translator.t(`labels.${label}`, options)
  },

  /**
   * Translate
   *
   * @param {string} path
   * @param {number} choice
   * @returns {string}
   */
  tc: (path, choice = 1) => transChoice(path, choice),

  /**
   * Checks if given translation exists
   *
   * @param {string} path
   */
  te: (path) => {
    const translations = Object.keys(
      I18n.getSharedInstance().activeMessages || {}
    )
    return translations.includes(path)
  },

  /**
   * Translates a Constant value
   *
   * @param constant
   * @param {string} value
   * @returns {string}
   */
  tConstName: (constant, value) => {
    if (!value) return ''

    constant = Formatter.plural(Formatter.snakeCase(constant))

    return Translator.t(`constants.${constant}.${value.toLowerCase().trim()}`)
  },

  /**
   * Tries to translate a value looking for everywhere posible
   * @param {string} string
   * @param {boolean} plural
   * @returns {string}
   */
  translate: (string, plural = false) => {
    if (!string) {
      return ''
    }

    if (Translator.te(`labels.${string}`)) {
      return Translator.tl(string)
    }

    if (Translator.te(`validation.attributes.${string}`)) {
      return Translator.ta(string)
    } else {
      const singularString = Formatter.singular(string)
      if (Translator.te(`validation.attributes.${singularString}`)) {
        return Translator.ta(singularString)
      }
    }

    const modelName = Formatter.singular(Formatter.snakeCase(string))

    if (Translator.te(`models.${modelName}`)) {
      return Translator.modelTitle(modelName, plural)
    }

    return Formatter.ucFirst(string)
  },

  /**
   * Translates an Enum
   *
   * @returns {string}
   * @param constant
   * @param value
   * @param plural
   */
  constantTitle: (constant, value, plural = false) => {
    if (!constant) return ''

    const snakeConstant = Formatter.snakeCase(constant)
    const pluralConstant = Formatter.plural(snakeConstant)

    return Translator.tc(`constants.${pluralConstant}.${value}`, plural ? 2 : 1)
  },

  /**
   * Translates a model name.
   *
   * @param {string} model
   * @param {boolean} plural
   * @returns {string}
   */
  modelTitle: (model, plural = false) => {
    if (!model) return ''

    const snakeModel = Formatter.snakeCase(model)
    const singularModel = Formatter.singular(snakeModel)

    return Translator.tc(`models.${singularModel}`, plural ? 2 : 1)
  },
  /**
   * Translation for a specific action and model
   *
   * @param {string} action
   * @param female
   * @param {string|null} model
   * @param plural
   * @returns {string}
   */
  actionTitle: (action, model = null, female = false, plural = false) => {
    let translatedAction

    if (model) {
      translatedAction = Translator.tc(`actions.${action}`, female ? 2 : 1)
    } else {
      translatedAction = Translator.t(`actions.${action}`)
    }

    if (model) {
      const translatedModel = Translator.modelTitle(model, plural)
      translatedAction = `${translatedAction} ${translatedModel}`
    }

    return translatedAction
  },
}

export default Translator
