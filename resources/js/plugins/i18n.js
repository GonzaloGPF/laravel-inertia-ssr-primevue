// import { createI18n } from 'vue-i18n';
import locales from '@/config/locales'
import { i18nVue } from 'laravel-vue-i18n'
import EventBus from '@/objects/EventBus.js'
import events from '@/config/events.js'

/*
 * All i18n resources specified in the plugin `include` option can be loaded
 * at once using the import syntax
 */
// import messages from '@intlify/vite-plugin-vue-i18n/messages';
//
// export default createI18n({
//     // allowComposition: true,
//     legacy: false, // you must set `false`, to use Composition API
//     numberFormats: locales.numbers,
//     dateTimeFormats: locales.dates,
//     locale: locales.getLocale(),
//     fallbackLocale: 'en',
//     messages: {
//         en: messages['php_en'],
//         es: messages['php_es'],
//     },
// });

export default i18nVue

export const options = {
    lang: locales.getLocale(),
    fallbackLocale: 'en',
    resolve: async lang => {
        const data = import.meta.glob('../../../lang/*.json')
        return await data[`../../../lang/php_${ lang }.json`]()
    },
    onLoad: () => {
        EventBus.emit(events.i18n_loaded)
    }
}
export const optionsSSR = {
    lang: locales.getLocale(),
    fallbackLocale: 'en',
    resolve: lang => {
        const data = import.meta.glob('../../../lang/*.json', { eager: true })
        return data[`../../../lang/php_${ lang }.json`].default
    },
    onLoad: () => {
        EventBus.emit(events.i18n_loaded)
    }
}
