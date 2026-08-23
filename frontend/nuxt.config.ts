import tailwindcss from "@tailwindcss/vite";
import { i18nPages } from './i18n/pages';

export default defineNuxtConfig({
  future: { compatibilityVersion: 4 },
  compatibilityDate: "2026-01-16",
  modules: [
    "@pinia/nuxt",
    "@nuxt/icon",
    "reka-ui",
    "nuxt-auth-utils",
    "@nuxtjs/i18n",
    '@vee-validate/nuxt',
    '@vueuse/nuxt'
  ],
  vite: {
    plugins: [
      tailwindcss(),
    ],
    define: {
      __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'true'
    }
  },
  pinia: {
    storesDirs: ['./stores/**'],
  },
  debug: true,
  css: ['~/assets/css/main.css'],
  nitro: {
    preset: 'bun'
  },
  i18n: {
    strategy: 'prefix_except_default',
    defaultLocale: 'fr',
    locales: [
      { code: 'fr', name: 'Français', file: 'fr.json' },
      { code: 'en', name: 'English', file: 'en.json' },
      { code: 'de', name: 'Deutsch', file: 'de.json' },
      { code: 'it', name: 'Italiano', file: 'it.json' }
    ],
    customRoutes: 'config',
    pages: i18nPages
  },
  runtimeConfig: {
    sessionPassword: '',
    public: {
      apiInternal: 'https://localhost:8443/api',
      apiCitySuggestions: 'https://api3.geo.admin.ch/rest/services/ech/SearchServer'
    }
  }
})