import colors from 'vuetify/es5/util/colors'

export default {
  // Disable server-side rendering: https://go.nuxtjs.dev/ssr-mode

  // Target: https://go.nuxtjs.dev/config-target

  head: {
    link: [
      { rel: 'preload', href: '/fonts/Uto-Bold.ttf', as: 'font', type: 'font/ttf', crossorigin: 'anonymous' },
      { rel: 'preload', href: '/img/logoBIG.svg', as: 'image' }
    ],
    meta: [
      { httpEquiv: 'Cache-Control', content: 'max-age=31536000, immutable' }
    ]
  },

  target: 'static',

  css: ['@/assets/css/global.css'],

  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    title: 'Do!t', // Il nome del sito che apparirà nella tab del browser

    htmlAttrs: {
      lang: 'it'
    },
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: '' },
      { name: 'format-detection', content: 'telephone=no' }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/img/logoSMALL.svg' }
    ]
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  modules: [
    '@nuxtjs/axios',
    '@nuxtjs/auth-next',
    'cookie-universal-nuxt',
  ],

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [
  ],

  // Auto import components: https://go.nuxtjs.dev/config-components
  components: true,

  // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
  buildModules: [
    // https://go.nuxtjs.dev/vuetify
    '@nuxtjs/vuetify'
  ],


  axios: {
    baseURL: 'http://localhost:8000',
    credentials: true
  },

  // Modules: https://go.nuxtjs.dev/config-modules
  auth: {
    strategies: {
      'laravelSanctum': {
        provider: 'laravel/sanctum',
        url: 'http://localhost:8000', // URL del tuo backend Laravel
        endpoints: {
          login: { url: '/api/login', method: 'post' },
          logout: { url: '/api/logout', method: 'post' },
          user: { url: '/api/user', method: 'get' }
        },
        token: {
          property: 'token',
          global: true,
          required: true,
          type: 'Bearer'
        },
      }
    },
    redirect: {
      login: '/',
      logout: '/',
      home: '/home',
      callback: false 
    }
  },

 
 



  // Vuetify module configuration: https://go.nuxtjs.dev/config-vuetify
  vuetify: {
    customVariables: [],
    
    theme: {
      options: {
        customProperties: true
      },
      dark: false,
      themes: {
        light: {
          primary: '#34495e',    // Blu Navy
          secondary: '#ffd166', // Giallo
          background: '#ffd966' // Beige
        },
        dark: {
          primary: '#ffd166',   // Giallo
          secondary: '#34495e', // Blu Navy
          background: '#222222' // Nero
        }
      }
    },
  },

  // Build Configuration: https://go.nuxtjs.dev/config-build
   build: {
    extend(config, { isClient }) {
      if (isClient) {
        config.node = {
          fs: 'empty',
          net: 'empty',
          tls: 'empty'
        }
      }
    }
  }
}
