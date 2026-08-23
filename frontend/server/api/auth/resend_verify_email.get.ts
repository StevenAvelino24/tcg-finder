import { i18nPages } from '~~/i18n/pages';
import type { Locale } from '~~/i18n/pages';

export default defineEventHandler(async (event) => {
    const query = getQuery(event);
    const config = useRuntimeConfig();
    const locale = getLocaleFromEvent(event) as Locale;
    const loginPath = i18nPages.login[locale] ?? i18nPages.login.fr

    try {
        await $fetch(`${config.public.apiInternal}/resend_verify_email`, {
            method: 'GET',
            query
        });

        return sendRedirect(event, `${loginPath}?resent=success`);
    } catch (error: any) {
        return sendRedirect(event, `${loginPath}?resent=error`);
    }
});