export default defineEventHandler(async (event) => {
    try {
        const cookie = getCookie(event, 'tcg_finder_token');

        if (cookie) deleteCookie(event, 'tcg_finder_token');
    } catch (error: any) {
        throw createError({
            statusCode: error.response?.status || 500,
            statusMessage: 'Error occured'
        });
    }
});