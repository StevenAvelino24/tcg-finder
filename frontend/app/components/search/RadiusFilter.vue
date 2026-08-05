<script setup lang="ts">
    const store = useSearchStore();

    const values = [
        {
            value: 0,
            text: '+0 km'
        },
        {
            value: 10,
            text: '+10 km'
        },
        {
            value: 20,
            text: '+20 km'
        },
        {
            value: 30,
            text: '+30 km'
        }
    ];
</script>

<template>
    <div class="flex-1 w-full px-3 py-2 hidden md:block">
        <Label for="radius" class="block text-xs font-bold text-gray-500 uppercase tracking-wider pb-2">{{ $t('search.radius.label') }}</Label>
        <SelectRoot id="radius" v-model="store.filters.radius">
            <SelectTrigger class="w-full inline-flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-200 rounded-md">
                <SelectValue placeholder="Select"></SelectValue>
                <Icon name="tabler:chevron-compact-down" class="w-4 h-4 opacity-50" />
            </SelectTrigger>
            <SelectPortal>
                <SelectContent
                    class="z-100 min-w-30 bg-white border-gray-200 rounded-lg shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-100"
                    position="popper"
                >
                    <SelectViewport class="p-1">
                        <SelectItem
                            v-for="value in values"
                            :key="value.value"
                            class="relative flex items-center px-8 py-2 text-sm rounded-md cursor-pointer select-none outline-none"
                            :value="value.value"
                        >
                            <SelectItemText>{{ value.text }}</SelectItemText>
                        </SelectItem>
                    </SelectViewport>
                </SelectContent>
            </SelectPortal>
        </SelectRoot>
    </div>
</template>