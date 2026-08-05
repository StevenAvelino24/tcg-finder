export default defineEventHandler(async (event) => {
    const body = await readBody(event);
    const config = useRuntimeConfig();

    try {
        return await $fetch(`${config.public.apiInternal}/register`, {
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