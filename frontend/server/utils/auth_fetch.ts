export const authFetch = (event: any, url: string, options: any = {}) => {
    const token = getCookie(event, 'tcg_finder_token');
    
    try {
        return $fetch(url, {
            ...options,
            headers: {
                ...options.headers,
                Authorization: token ? `Bearer ${token}` : ''
            }
        });
    }
    catch (err: any) {
        if (err.response?.status === 401) {
            deleteCookie(event, 'tcg_finder_token')
        }
        throw err
    }
}