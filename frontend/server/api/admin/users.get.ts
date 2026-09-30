export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    const query = getQuery(event);

    try {
        return await authFetch(event, `${config.public.apiInternal}/admin/users`, {
            method: 'GET',
            query
        });
    }
    catch (error: any) {
        throw createError({
            statusCode: 500,
            statusMessage: 'Failed to fetch users from backend'
        });
    }
});