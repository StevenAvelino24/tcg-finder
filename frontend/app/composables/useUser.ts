export interface User {
    email: string,
    firstName?: string,
    lastName?: string,
    roles: string[],
    hasShop: boolean,
};

export const useUser = () => {
    const user = useState<User | null>('auth-user', () => null);

    const isLoggedIn = computed(() => !!user.value);
    const isAdmin = computed(() => user.value?.roles.includes('ROLE_ADMIN'));
    const hasRole = (role: string) => user.value?.roles.includes(role);

    return { user, isLoggedIn, isAdmin, hasRole };
}