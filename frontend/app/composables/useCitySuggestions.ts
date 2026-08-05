import type { LocationResult, SearchResponse } from "~/types/geo";

export const useCitySuggestions = (searchTerm: Ref<string>) => {
    const config = useRuntimeConfig()
    const debouncedSearch = refDebounced(searchTerm, 350)
    const shouldSearch = computed(() => debouncedSearch.value.length >= 2)

    const { data, status, error, refresh } = useAsyncData<LocationResult[]>(
        'city-suggestions',
        async () => {
            if (!shouldSearch.value) return []

            const res = await $fetch<SearchResponse>(config.public.apiCitySuggestions, {
                params: {
                    searchText: debouncedSearch.value,
                    type: 'locations',
                    origins: 'gg25',
                    limit: 5
                }
            })

            return res.results || []
        },
        {
            watch: [debouncedSearch],
            transform: (results) => results.map(res => ({
                ...res,
                attrs: {
                    ...res.attrs,
                    label: res.attrs.label.replace(/<[^>]*>?/gm, '')
                }
            }))
        }
    )

    return {
        suggestions: data,
        isLoading: computed(() => status.value === 'pending'),
        error,
        refresh
    }
}