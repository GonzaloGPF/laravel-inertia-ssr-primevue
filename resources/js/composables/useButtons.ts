import type { ButtonProps } from '@/types/buttons'
import { buttons } from '@/config/buttons'
import { Translator } from '@/objects/Translator'
import { Formatter } from '@/objects/Formatter'
import { ActionName } from '@/types/actions'

type Overwrites =
  ButtonProps | ((action: ActionName) => ButtonProps | undefined)

export function useButtons() {
  const getButtons = (
    actions: ActionName[] = [],
    overwrites?: Overwrites
  ): ButtonProps[] => {
    const staticOverwrite =
      typeof overwrites === 'function' ? undefined : overwrites

    return actions.flatMap((action) => {
      const buttonData = buttons[action]

      if (buttonData == null) {
        return []
      }

      const overwriteData =
        typeof overwrites === 'function' ? overwrites(action) : staticOverwrite

      return [
        {
          ...buttonData,
          action,
          ...(overwriteData ?? {}),
          label: getLabel((overwriteData ?? buttonData).label, action),
        },
      ]
    })
  }

  return { getButtons }
}

const getLabel = (
  label: ButtonProps['label'],
  action: ButtonProps['action']
) => {
  if (typeof label === 'function') {
    return label(action)
  }

  if (typeof label === 'string') {
    return label
  }

  const path = `actions.${action}`

  if (Translator.te(path)) {
    return Translator.t(path)
  }

  return Formatter.ucFirst(String(action))
}
