import { i18nPages } from '~~/i18n/pages';
import type { Locale } from '~~/i18n/pages';

export default defineEventHandler(async (event) => {
    const query = getQuery(event);
    const config = useRuntimeConfig();
    const locale = getLocaleFromEvent(event) as Locale;
    const loginPath = i18nPages.login[locale] ?? i18nPages.login.fr

    try {
        await $fetch(`${config.public.apiInternal}/verify_email`, {
            method: 'GET',
            query
        });

        return sendRedirect(event, `${loginPath}?verified=success`);
    } catch (error: any) {
        return sendRedirect(event, `${loginPath}?verified=error`);
    }
});