<script setup lang="ts">
    const { games, fetchGames } = useGames();
    const store = useSearchStore();
    await fetchGames();
</script>

<template>
    <div class="flex-1 w-full px-3 py-2 hidden md:block">
        <Label for="game" class="block text-xs font-bold text-gray-500 uppercase tracking-wider pb-2">{{ $t('search.game.label') }}</Label>
        <SelectRoot id="game" v-model="store.filters.game">
            <SelectTrigger class="w-full inline-flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-200 rounded-md">
                <SelectValue :placeholder="$t('search.game.placeholder')"></SelectValue>
                <Icon name="tabler:chevron-compact-down" class="w-4 h-4 opacity-50" />
            </SelectTrigger>
            <SelectPortal>
                <SelectContent
                    class="z-100 min-w-30 bg-white border-gray-200 rounded-lg shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-100"
                    position="popper"
                >
                    <SelectViewport class="p-1">
                        <SelectItem
                            v-for="game in games"
                            :key="game.name"
                            :value="game.name"
                            class="relative flex items-center px-8 py-2 text-sm rounded-md cursor-pointer select-none outline-none"
                        >
                            <SelectItemText>{{ game.name }}</SelectItemText>
                        </SelectItem>
                    </SelectViewport>
                </SelectContent>
            </SelectPortal>
        </SelectRoot>
    </div>
</template>