export default defineNuxtRouteMiddleware((to) => {
    const { user } = useUser();
    const localePath = useLocalePath();

    if (user?.value?.hasShop) {
        return navigateTo(localePath('profile'));
    }
})