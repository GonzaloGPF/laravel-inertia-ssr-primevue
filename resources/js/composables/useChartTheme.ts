import { onBeforeUnmount, onMounted, ref } from 'vue'

export type ChartTheme = {
  primary: string
  primaryFill: string
  text: string
  muted: string
  grid: string
}

const LIGHT_THEME: ChartTheme = {
  primary: 'rgb(16, 185, 129)',
  primaryFill: 'rgba(16, 185, 129, 0.15)',
  text: 'rgb(51, 65, 85)',
  muted: 'rgb(100, 116, 139)',
  grid: 'rgb(226, 232, 240)',
}

const canvas = () => {
  const element = document.createElement('canvas')
  element.width = element.height = 1

  return element.getContext('2d', { willReadFrequently: true })
}

/**
 * The rgb of the color a css value paints. The canvas of a chart cannot read variables, and the colors of the theme
 * come as them (and in any color space), so the browser resolves it and a pixel of a canvas says what it is.
 */
const toRgb = (cssValue: string): [number, number, number] => {
  const probe = document.createElement('span')
  probe.style.color = cssValue
  document.body.appendChild(probe)
  const resolved = getComputedStyle(probe).color
  probe.remove()

  const context = canvas()
  if (!context) return [0, 0, 0]

  context.fillStyle = resolved
  context.fillRect(0, 0, 1, 1)
  const [red, green, blue] = context.getImageData(0, 0, 1, 1).data

  return [red, green, blue]
}

const rgb = (color: [number, number, number], alpha = 1) =>
  alpha === 1
    ? `rgb(${color.join(', ')})`
    : `rgba(${color.join(', ')}, ${alpha})`

const readTheme = (): ChartTheme => {
  const primary = toRgb('var(--p-primary-color)')

  return {
    primary: rgb(primary),
    primaryFill: rgb(primary, 0.15),
    text: rgb(toRgb('var(--p-text-color)')),
    muted: rgb(toRgb('var(--p-text-muted-color)')),
    grid: rgb(toRgb('var(--p-content-border-color)')),
  }
}

/**
 * The colors of the PrimeVue theme in use, for the charts. They follow the light and dark modes of the system.
 * Until the page is in the browser (the server cannot read them), it is the light theme.
 */
export default function useChartTheme() {
  const theme = ref<ChartTheme>(LIGHT_THEME)
  const darkMode =
    typeof window === 'undefined'
      ? undefined
      : window.matchMedia('(prefers-color-scheme: dark)')

  const update = () => (theme.value = readTheme())

  onMounted(() => {
    update()
    darkMode?.addEventListener('change', update)
  })

  onBeforeUnmount(() => darkMode?.removeEventListener('change', update))

  return theme
}
