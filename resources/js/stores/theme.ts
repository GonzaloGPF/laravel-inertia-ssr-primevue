import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import type { ThemeMode } from '@/types/theme'

// The same ones as in the script of the head of app.blade.php, which sets the theme before the page is painted
const STORAGE_KEY = 'theme'
const DARK_CLASS = 'app-dark'
const MODES: ThemeMode[] = ['light', 'dark', 'system']

const storedMode = (): ThemeMode => {
  try {
    const stored = localStorage.getItem(STORAGE_KEY) as ThemeMode

    return MODES.includes(stored) ? stored : 'system'
  } catch {
    return 'system' // The storage can be blocked
  }
}

/**
 * The theme of the application: light, dark, or the one of the system, which it follows when it changes.
 * The dark one is the `app-dark` class of the `html` element, which PrimeVue and the `dark:` variant of Tailwind use.
 * The choice is kept in the browser.
 */
export const useTheme = defineStore('theme', () => {
  const browser = typeof window !== 'undefined'
  const darkSystem = browser
    ? window.matchMedia('(prefers-color-scheme: dark)')
    : null

  const mode = ref<ThemeMode>(browser ? storedMode() : 'system')
  const systemIsDark = ref(!!darkSystem?.matches)
  const isDark = computed(
    () =>
      mode.value === 'dark' || (mode.value === 'system' && systemIsDark.value)
  )

  darkSystem?.addEventListener('change', (event) => {
    systemIsDark.value = event.matches
  })

  if (browser) {
    watch(
      isDark,
      (dark) => document.documentElement.classList.toggle(DARK_CLASS, dark),
      { immediate: true }
    )
  }

  const setMode = (value: ThemeMode) => {
    mode.value = value

    try {
      localStorage.setItem(STORAGE_KEY, value)
    } catch {
      // The choice lasts until the page is closed
    }
  }

  return { mode, isDark, setMode }
})
