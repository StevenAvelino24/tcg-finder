<script setup lang="ts">
    const store = useSearchStore();

    const indexValues = [
        {
            value: 'events',
            text: $t('search.form.index.events')
        },
        {
            value: 'shops',
            text: $t('search.form.index.shops')
        }
    ];
</script>

<template>
    <div class="flex-1 w-full px-3 py-2 hidden md:block">
        <Label for="index" class="block text-xs font-bold text-gray-500 uppercase tracking-wider pb-2">{{ $t('search.index.label') }}</Label>
        <SelectRoot id="index" v-model="store.filters.index">
            <SelectTrigger class="w-full inline-flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-200 rounded-md">
                <span class="flex-1 min-w-0 truncate text-left">
                    <SelectValue placeholder="Select"></SelectValue>
                </span>
                <Icon name="tabler:chevron-compact-down" class="w-4 h-4 opacity-50 shrink-0" />
            </SelectTrigger>
            <SelectPortal>
                <SelectContent
                    class="z-100 min-w-30 bg-white border-gray-200 rounded-lg shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-100"
                    position="popper"
                >
                    <SelectViewport class="p-1">
                        <SelectItem
                            v-for="indexValue in indexValues"
                            :key="indexValue.value"
                            :value="indexValue.value"
                            class="relative flex items-center px-8 py-2 text-sm rounded-md cursor-pointer select-none outline-none"
                        >
                            <SelectItemText>{{ indexValue.text }}</SelectItemText>
                        </SelectItem>
                    </SelectViewport>
                </SelectContent>
            </SelectPortal>
        </SelectRoot>
    </div>
</template>