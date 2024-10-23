import globals from 'globals'
import pluginJs from '@eslint/js'
import tseslint from 'typescript-eslint'
import pluginVue from 'eslint-plugin-vue'

export default [
  pluginJs.configs.recommended,
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/essential'],
  {
    files: ['**/resources/js/**/*.{js,mjs,cjs,ts,vue}'],
    languageOptions: {
      globals: globals.browser,
      parserOptions: {
        parser: tseslint.parser,
      },
    },
    rules: {
      // 'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
      // 'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
      'vue/multi-word-component-names': 'off',
      'no-undef': 'off',
    },
  },
  {
    ignores: [
      '**/node_modules/*',
      '**/vendor/*',
      '**/public/build/*',
      '**/*.d.ts',
      '**/bootstrap/*',
    ],
  },
]
