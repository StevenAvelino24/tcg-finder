export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    const id = getRouterParam(event, 'id');

    try {
        await authFetch(event, `${config.public.apiInternal}/admin/users/${id}`, {
            method: 'DELETE',
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