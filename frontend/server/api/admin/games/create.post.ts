export default defineEventHandler(async (event) => {
    const body = await readBody(event);
    const config = useRuntimeConfig();

    try {
        await authFetch(event, `${config.public.apiInternal}/admin/games`, {
            method: 'POST',
            body: body
        });
    }
    catch (error: any) {
        throw createError({
            statusCode: error.response?.status || 500,
            statusMessage: 'An error ocurred',
            data: error.data
        });
    }
});