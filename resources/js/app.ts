import '../css/app.css'

import { createApp, createSSRApp, h, type DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { app } from '@/config/app'
import BaseLayout from '@/Layouts/BaseLayout.vue'
import { registerDirectives } from '@/directives/primevue'
import { registerPlugins } from '@/plugins/registerPlugins'

createInertiaApp({
  title: (title) => app.getAppTitle(title),
  resolve: (name) => {
    const page = resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue')
    )

    page.then((module) => {
      module.default.layout = module.default.layout || BaseLayout
    })

    return page
  },
  setup({ el, App, props, plugin }) {
    // Hydrate only when the server rendered the page: without the SSR server the container is empty
    const create = el.hasChildNodes() ? createSSRApp : createApp
    const app = create({ render: () => h(App, props) }).use(plugin)

    registerPlugins(app, props)
    registerDirectives(app)

    app.mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
