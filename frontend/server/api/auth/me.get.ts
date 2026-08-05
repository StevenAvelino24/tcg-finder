import { jwtDecode } from 'jwt-decode'

export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    const cookie = getCookie(event, 'tcg_finder_token');

    if (!cookie) return null;

    try {
        const decoded = jwtDecode(cookie)

        if (decoded.exp && decoded.exp * 1000 < Date.now()) {
            deleteCookie(event, 'tcg_finder_token')
            return null;
        }
    } catch (error: any) {
        return null
    }
    
    try {
        return await authFetch(event, `${config.public.apiInternal}/backend/user`);
    }
    catch (error: any) {
        return null;
    }
});