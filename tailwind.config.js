import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import tailwindCss from 'tailwindcss-primeui'
import colors from './resources/js/config/colors.js'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],

  theme: {
    colors,
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
    },
  },

  safelist: Object.keys(colors)
    .flatMap(className => [`bg-${className}`, className, `text-${className}`]),

  plugins: [forms, tailwindCss],
}
