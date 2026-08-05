interface SearchFilters {
    game: string | null;
    index: string;
    radius: number;
    city: string | null;
    topLeft: { lat: number; lon: number } | null;
    bottomRight: { lat: number; lon: number } | null;
}

export const useSearchStore = defineStore('search', () => {
    const filters = ref<SearchFilters>({
        game: null,
        index: '',
        radius: 5,
        city: null,
        topLeft: null,
        bottomRight: null
    });

    return { filters }
});