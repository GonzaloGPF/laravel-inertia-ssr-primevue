import { onBeforeUnmount, onMounted, ref } from 'vue'
import type { ChartColor, ChartTheme } from '@/types/chart'

type Rgb = [number, number, number]

// The first one is the primary color of the theme, the rest are the same shade of the colors of its palette
const PALETTE = [
  'primary',
  'sky',
  'amber',
  'violet',
  'rose',
  'teal',
  'orange',
  'indigo',
  'lime',
  'fuchsia',
]

const rgb = (color: Rgb, alpha = 1) =>
  alpha === 1
    ? `rgb(${color.join(', ')})`
    : `rgba(${color.join(', ')}, ${alpha})`

const chartColor = (color: Rgb): ChartColor => ({
  color: rgb(color),
  fill: rgb(color, 0.15),
})

const LIGHT_THEME: ChartTheme = {
  text: 'rgb(51, 65, 85)',
  muted: 'rgb(100, 116, 139)',
  grid: 'rgb(226, 232, 240)',
  surface: 'rgb(255, 255, 255)',
  series: [
    [16, 185, 129],
    [14, 165, 233],
    [245, 158, 11],
    [139, 92, 246],
    [244, 63, 94],
    [20, 184, 166],
    [249, 115, 22],
    [99, 102, 241],
    [132, 204, 22],
    [217, 70, 239],
  ].map((color) => chartColor(color as Rgb)),
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
const toRgb = (cssValue: string): Rgb => {
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

const readTheme = (dark: boolean): ChartTheme => ({
  text: rgb(toRgb('var(--p-text-color)')),
  muted: rgb(toRgb('var(--p-text-muted-color)')),
  grid: rgb(toRgb('var(--p-content-border-color)')),
  surface: rgb(toRgb('var(--p-content-background)')),
  series: PALETTE.map((name) =>
    chartColor(
      toRgb(
        name === 'primary'
          ? 'var(--p-primary-color)'
          : `var(--p-${name}-${dark ? 400 : 500})`
      )
    )
  ),
})

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

  const update = () => (theme.value = readTheme(!!darkMode?.matches))

  onMounted(() => {
    update()
    darkMode?.addEventListener('change', update)
  })

  onBeforeUnmount(() => darkMode?.removeEventListener('change', update))

  return theme
}
