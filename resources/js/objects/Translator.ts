import {
  getActiveLanguage,
  loadLanguageAsync,
  trans,
  transChoice,
} from 'laravel-vue-i18n'
import { Formatter } from '@/objects/Formatter'
import { es, Locale } from 'date-fns/locale'

export const Translator = {
  getLocale: () => getActiveLanguage(),

  getDateLocale() {
    const locales: Record<string, Locale> = {
      es,
    }

    return locales[Translator.getLocale()]
  },

  setLocale(locale?: string) {
    if (!locale) return

    document.documentElement.setAttribute('lang', locale)

    return loadLanguageAsync(locale)
  },

  t: (string: string, options = {}) => trans(string, options),

  /**
   * Translates an attribute
   */
  ta: (attribute?: string) => {
    if (!attribute) return ''

    return Formatter.title(Translator.t(`validation.attributes.${attribute}`))
  },

  /**
   * Translates a label
   */
  tl: (label?: string, options = {}) => {
    if (!label) return ''

    return Translator.t(`labels.${label}`, options)
  },

  /**
   * Translate
   */
  tc: (path: string, choice = 1) => transChoice(path, choice),

  /**
   * Checks if given translation exists
   */
  te: (path?: string) => {
    if (!path) return false

    const translated = trans(path)

    return translated !== path
  },

  /**
   * Translates a Constant value
   */
  tConstName: (constant: string, value?: string) => {
    if (!value) return ''

    constant = Formatter.plural(Formatter.snakeCase(constant))

    return Translator.t(`constants.${constant}.${value.toLowerCase().trim()}`)
  },

  /**
   * Tries to translate a value looking for everywhere possible
   */
  translate: (string: string, plural = false) => {
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
   */
  constantTitle: (constant: string, value: string, plural = false) => {
    if (!constant) return ''

    const snakeConstant = Formatter.snakeCase(constant)
    const pluralConstant = Formatter.plural(snakeConstant)

    return Translator.tc(`constants.${pluralConstant}.${value}`, plural ? 2 : 1)
  },

  /**
   * Translates a model name.
   */
  modelTitle: (model: string, plural = false) => {
    if (!model) return ''

    const snakeModel = Formatter.snakeCase(model)
    const singularModel = Formatter.singular(snakeModel)

    return Translator.tc(`models.${singularModel}`, plural ? 2 : 1)
  },

  /**
   * Translation for a specific action and model
   */
  actionTitle: (
    action: string,
    model: string = '',
    female = false,
    plural = false
  ) => {
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
