export default defineNuxtRouteMiddleware((to) => {
    const { user, hasRole } = useUser();
    const localePath = useLocalePath();

    if (!user.value) {
        return navigateTo(localePath('login'));
    }

    if (!hasRole('ROLE_ADMIN')) {
        return navigateTo(localePath('index'));
    }
})