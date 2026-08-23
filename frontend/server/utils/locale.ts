export function getLocaleFromEvent(event: H3Event): string {
  const cookieLocale = getCookie(event, 'i18n_redirected');
  if (cookieLocale) return cookieLocale;

  const acceptLanguage = getHeader(event, 'accept-language');
  const preferred = acceptLanguage?.split(',')[0]?.split('-')[0];

  const supported = ['en', 'fr'];
  return supported.includes(preferred ?? '') ? preferred! : 'fr';
}