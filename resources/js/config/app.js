export default {
    /**
     *  Application environment data from Laravel Mix (.env file)
     */
    env: {
        environment: import.meta.env.VITE_APP_ENV,
        debug: import.meta.env.VITE_APP_DEBUG,
        broadcastDriver: import.meta.env.VITE_BROADCAST_DRIVER,
        pusherAppKey: import.meta.env.VITE_PUSHER_APP_KEY,
        pusherAppCluster: import.meta.env.VITE_PUSHER_APP_CLUSTER
        // stripeKey: import.meta.env.VITE_STRIPE_KEY,
    },

    /**
     * The app url is given by Laravel in app.blade.php file
     *
     * @returns string
     */
    getAppURL (path = null) {
        const baseUrl = location.protocol + '//' + window.location.hostname

        if (! path) return baseUrl

        if (! path.startsWith('/')) {
            path = `/${ path }`
        }

        return `${ baseUrl }${ path }`
    },

    /**
     * The app name given by Laravel in .env (APP_NAME)
     *
     * @returns {string}
     */
    getAppName () {
        // const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
        return document.head.querySelector('meta[name="app_name"]').content
    },

    /**
     * Dynamic title of current page
     *
     * @returns {string}
     */
    getAppTitle (title) {
        const appName = this.getAppName()

        if (title && title !== appName) {
            return `${ title } - ${ appName }`
        }

        return appName
    },

    /**
     * Company name is defined in mobius.php
     *
     * @returns {*}
     */
    getCompanyName () {
        return document.head.querySelector('meta[name="company_name"]').content
    },

    /**
     * Detects if its in local environment
     *
     * @returns {boolean}
     */
    isLocal () {
        if (! this.env.environment) return true

        const environment = this.env.environment.toLowerCase()

        return environment === 'local'
    }
}
