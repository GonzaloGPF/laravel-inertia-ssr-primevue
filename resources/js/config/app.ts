import { MetaHTMLAttributes } from 'vue'

export const app = {
  /**
   *  Application environment data from Laravel Mix (.env file)
   */
  env: {
    environment: import.meta.env.VITE_APP_ENV,
    debug: import.meta.env.VITE_APP_DEBUG,
    broadcastDriver: import.meta.env.VITE_BROADCAST_DRIVER,
    pusherAppKey: import.meta.env.VITE_PUSHER_APP_KEY,
    pusherAppCluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    // stripeKey: import.meta.env.VITE_STRIPE_KEY,
  },

  /**
   * The app url is given by Laravel in the app.blade.php file
   */
  getAppURL(path = '') {
    const baseUrl = location.protocol + '//' + window.location.hostname

    if (!path) return baseUrl

    if (!path.startsWith('/')) {
      path = `/${path}`
    }

    return `${baseUrl}${path}`
  },

  /**
   * The app name given by Laravel in .env (APP_NAME)
   */
  getAppName() {
    const metaTag = document.head.querySelector('meta[name="app_name"]')
    // const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

    return (metaTag as MetaHTMLAttributes)?.content
  },

  /**
   * Dynamic title of the current page
   */
  getAppTitle(title = '') {
    const appName = this.getAppName() ?? ''

    if (title && title !== appName) {
      return `${title} - ${appName}`
    }

    return appName
  },

  /**
   * Company name is defined in mobius.php
   */
  getCompanyName() {
    const metaTag = document.head.querySelector('meta[name="company_name"]')

    return (metaTag as MetaHTMLAttributes)?.content
  },

  /**
   * Detects if its in local environment
   */
  isLocal() {
    if (!this.env.environment) return true

    const environment = this.env.environment.toLowerCase()

    return environment === 'local'
  },
}
