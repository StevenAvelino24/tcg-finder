export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();

    try {
        return await $fetch(`${config.public.apiInternal}/games`, {
            method: 'GET'
        });
    }
    catch (error: any) {
        throw createError({
            statusCode: 500,
            statusMessage: 'Failed to fetch games from backend'
        });
    }
});