export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();

    try {
        await authFetch(event, `${config.public.apiInternal}/backend/user`, {
            method: 'DELETE',
        });
        const cookie = getCookie(event, 'tcg_finder_token');
        if (cookie) deleteCookie(event, 'tcg_finder_token');
    }
    catch (error: any) {
        throw createError({
            statusCode: error.response?.status || 500,
            statusMessage: error.data,
            data: error.data
        });
    }
});