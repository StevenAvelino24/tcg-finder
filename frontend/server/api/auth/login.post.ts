export default defineEventHandler(async (event) => {
    const body = await readBody(event);
    const config = useRuntimeConfig();

    try {
        const response = await $fetch<{ token: string }>(`${config.public.apiInternal}/login_check`, {
            method: 'POST',
            body
        });

        setCookie(event, 'tcg_finder_token', response.token, {
            httpOnly: true,
            secure: true,
            sameSite: 'lax',
            maxAge: 60 * 60 * 24
        });

        return { success: true };
    }
    catch (error: any) {
        throw createError({
            statusCode: error.response?.status || 401,
            statusMessage: 'Invalid credentials'
        });
    }
});