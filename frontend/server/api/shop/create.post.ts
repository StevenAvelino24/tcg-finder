export default defineEventHandler(async (event) => {
    const body = await readBody(event);
    const config = useRuntimeConfig();

    try {
        return await authFetch(event, `${config.public.apiInternal}/backend/shops`, {
            method: 'POST',
            body: body
        });
    }
    catch (error: any) {
        throw createError({
            statusCode: error.response?.status || 500,
            statusMessage: error.data,
            data: error.data
        });
    }
});