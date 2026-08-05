<script setup lang="ts">
    import type { LocationResult } from '~/types/geo';

    const searchStore = useSearchStore();
    const cityInput = ref(searchStore.filters.city || '');
    const { suggestions, isLoading, error } = useCitySuggestions(cityInput);

    const updateCityFilters = (suggestion: LocationResult) => {
        searchStore.filters.city = suggestion.attrs.label;
        const { topLeft, bottomRight } = getGeoBoundingBox(suggestion.attrs.lat, suggestion.attrs.lon, searchStore.filters.radius);
        searchStore.filters.topLeft = topLeft;
        searchStore.filters.bottomRight = bottomRight;
    };
</script>

<template>
    <div class="flex-1 w-full px-3 py-2 hidden md:block">
        <Label for="city" class="block text-xs font-bold text-gray-500 uppercase tracking-wider pb-2">{{ $t('search.city.label') }}</Label>
        <ComboboxRoot id="city">
            <ComboboxAnchor class="w-full inline-flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-200 rounded-md">
                <ComboboxInput
                    v-model="cityInput"
                    class="bg-transparent text-sm font-medium focus:outline-none w-full placeholder:text-gray-300"
                />
            </ComboboxAnchor>
            <ComboboxContent
                class="absolute bg-white shadow-2xl rounded-b-2xl border-t z-200 mt-2"
            >
                <div v-if="isLoading" class="p-4 text-center text-sm text-gray-500">
                    {{ $t('search.city.loading') }}
                </div>
                
                <div v-else-if="error" class="p-4 text-center text-sm text-red-500">
                    {{ $t('search.city.error') }}
                </div>

                <ComboboxViewport v-else class="p-2 max-h-75 overflow-y-auto">
                    <div v-if="suggestions && suggestions.length === 0 && cityInput.length >= 2" class="p-2 text-center text-sm text-gray-500">
                        {{ $t('search.city.no_result') }}
                    </div>

                    <ComboboxItem
                        v-for="suggestion in suggestions || []"
                        :key="suggestion.attrs.label"
                        :value="suggestion.attrs.label"
                        @click="updateCityFilters(suggestion)"
                        class="relative flex items-center h-8 px-2 text-sm text-gray-700 hover:bg-gray-100 rounded select-none cursor-pointer data-[highlighted]:bg-gray-100 data-[highlighted]:text-gray-900 outline-none"
                    >
                        {{ suggestion.attrs.label }}
                    </ComboboxItem>
                </ComboboxViewport>
            </ComboboxContent>
        </ComboboxRoot>
    </div>
</template>