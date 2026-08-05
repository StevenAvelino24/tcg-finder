export default defineNuxtRouteMiddleware(async (to) => {
    const { user } = useUser();

    if (user.value) return;

    try {
        const fetcher = useRequestFetch();
        user.value = await fetcher('/api/auth/me');
    } catch (e) {
        user.value = null;
    }
})