export type Locale = 'en' | 'fr' | 'de' | 'it';

type LocalizedPaths = Record<Locale, string>;

interface I18nPages {
  login: LocalizedPaths,
  register: LocalizedPaths,
  search: LocalizedPaths,
  profile: LocalizedPaths,
  'shop/create': LocalizedPaths,
  'shop/portal': LocalizedPaths
}

export const i18nPages: I18nPages = {
    'register': {
        fr: '/s-inscrire',
        en: '/register',
        de: '/register',
        it: '/register'
    },
    'search': {
        fr: '/recherche',
        en: '/search',
        de: '/search',
        it: '/search'
    },
    'login': {
        fr: '/se-connecter',
        en: '/login',
        de: '/login',
        it: '/login'
    },
    'profile': {
        fr: '/profil',
        en: '/profile',
        de: '/profile',
        it: '/profile'
    },
    'shop/create': {
        fr: '/magasin/creer',
        en: '/shop/create',
        de: '/shop/create',
        it: '/shop/create'
    },
    'shop/portal': {
        fr: '/magasin/portail',
        en: '/shop/portal',
        de: '/shop/portal',
        it: '/shop/portal'
    }
};