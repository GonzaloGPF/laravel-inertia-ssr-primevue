npm — bastante que mover

Minors/patch (seguros, recomendado ya): vue/@vue/server-renderer 3.5.16→3.5.43, axios 1.9→1.20, tailwindcss+@tailwindcss/vite 4.1→4.3, date-fns, prettier, typescript-eslint, eslint-plugin-vue, laravel-vue-i18n, lodash, @types/lodash.

Majors (con cuidado): @inertiajs/vue3 2→3, pinia 3→4, primevue 4→5, vite 6→8, @vitejs/plugin-vue 5→6, laravel-vite-plugin 1→3, vue-tsc 2→3, typescript 5.8→7, eslint 9→10, stylelint 16→17, globals, lint-staged.

🔎 Lo que me llama la atención (lo más valioso)
tailwind.config.js está huérfano en Tailwind v4. Tu app.css hace @import "tailwindcss" sin @config, así que en v4 ese fichero no se carga. Eso significa que tu paleta de colores custom (config/colors), la fuente Figtree, el safelist y el plugin forms no se están aplicando vía utilidades Tailwind. Es una laguna de la migración v3→v4. Se arregla con @config "../../tailwind.config.js" en el CSS, o migrando a config nativa v4 (@theme, @plugin, @source).
autoprefixer — innecesario en Tailwind v4 (Lightning CSS ya prefija) y no hay postcss.config. Eliminable.
@tailwindcss/forms — solo lo referencia el config huérfano y usáis inputs de PrimeVue. Sobra (o se activa con @plugin si lo queréis).
lint-staged declarado pero sin husky ni configuración → dependencia muerta (a menos que queráis cablear un pre-commit).
@primevue/themes (4.3.5) va por detrás de primevue (4.5.4) — deberían ir a la par; conviene alinearlos.
Inertia: si actualizáis, server (3) y cliente (@inertiajs/vue3 3) van en pareja, no sueltos.
primevue 5 está disponible, pero yo lo aguantaría: estáis en plena migración a PrimeVue 4; saltar a 5 a la vez mezcla dos cosas y multiplica el riesgo.
typescript 7 y vite 8 son saltos gordos (TS 7 es el compilador nativo nuevo; Vite 8 arrastra @vitejs/plugin-vue 6 + laravel-vite-plugin 3). No los metería ahora sin una tanda dedicada.
Cómo lo plantearía (por tandas, de menor a mayor riesgo)
A) Majors de tooling contenidos — vue-tsc 3, eslint 10 (+plugins), stylelint 17, pinia 4. Riesgo medio, verificable con build+lint.
B) Majors coordinados — Inertia v3 (server+cliente) y Pest 5. Requieren revisar breaking changes y correr toda la suite.
C) Aparcar por ahora — primevue 5, typescript 7, vite 8.