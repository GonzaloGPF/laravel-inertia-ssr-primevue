import Formatter from '@/objects/Formatter.js'
import Translator from '@/objects/Translator.js'
import { usePage } from '@inertiajs/vue3'

export default function useConstants() {
  const { props } = usePage()

  const getConstants = (constantName) => {
    constantName = Formatter.plural(Formatter.snakeCase(constantName)) // A bit of normalization

    return (props.constants[constantName] || []).map((name) => ({
      id: name,
      label: Translator.tConstName(constantName, name),
    }))
  }

  const existsConstant = (constantName) => !!getConstants(constantName)[0]

  return {
    getConstants,
    existsConstant,
  }
}
