import { Formatter } from '@/objects/Formatter.js'
import { Translator } from '@/objects/Translator'
import { usePage } from '@inertiajs/vue3'

export function useConstants() {
  const { props } = usePage()

  const getConstants = (constantName?: string) => {
    const constants = props.constants as Record<string, string[]>
    constantName = Formatter.plural(Formatter.snakeCase(constantName)) // A bit of normalization

    return (constants[constantName] || []).map((name) => ({
      id: name,
      label: Translator.tConstName(constantName, name),
    }))
  }

  const existsConstant = (constantName?: string) => !!getConstants(constantName)[0]

  return {
    getConstants,
    existsConstant,
  }
}
