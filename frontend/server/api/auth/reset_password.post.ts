export default defineEventHandler(async (event) => {
    const body = await readBody(event);
    const config = useRuntimeConfig();
    console.log(body);

    try {
        return await $fetch(`${config.public.apiInternal}/reset_password`, {
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