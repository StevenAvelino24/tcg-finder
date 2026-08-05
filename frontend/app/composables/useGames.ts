import type { Game } from "~/types/game";

export const useGames = () => {
    const games = useState<Game[]>('games-list', () => []);
    const nuxtApp = useNuxtApp();

    const fetchGames = async () => {
        if (games.value.length > 0) return;

        try {
            const { data } = await useFetch<Game[]>('/api/games/list', {
                key: 'games-list-api-call',
                getCachedData(key) {
                    return nuxtApp.payload.data[key] || nuxtApp.static.data[key]
                },
                deep: false
            });

            if (data.value) {
                games.value = data.value;
            }
        }
        catch (error: any) {
            return [];
        }
    }

    return { games, fetchGames };
}