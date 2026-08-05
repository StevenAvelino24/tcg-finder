<script setup lang="ts">
    const { locale, locales } = useI18n()
    const switchLocalePath = useSwitchLocalePath()

    const availableLocales = computed(() => locales.value)
    const currentLocaleName = computed(() => 
        availableLocales.value.find(l => l.code === locale.value)?.name || locale.value
    )

    async function handleLocaleChange(newLocale: "fr" | "en" | "de" | "it") {
        const path = switchLocalePath(newLocale)
        if (path) {
            await navigateTo(path)
        }
    }
</script>

<template>
    <SelectRoot :model-value="locale" @update:model-value="handleLocaleChange">
        <SelectTrigger class="inline-flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium bg-white border border-gray-200 rounded-md">
            <SelectValue>{{ currentLocaleName }}</SelectValue>
            <Icon name="tabler:chevron-compact-down" class="w-4 h-4 opacity-50" />
        </SelectTrigger>

        <SelectPortal>
            <SelectContent
                class="z-100 min-w-30 bg-white border-gray-200 rounded-lg shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-100"
                position="popper"
            >
                <SelectViewport class="p-1">
                    <SelectItem 
                        v-for="l in availableLocales" 
                        :key="l.code" 
                        :value="l.code"
                        class="relative flex items-center px-8 py-2 text-sm rounded-md cursor-pointer select-none outline-none"
                    >
                        <SelectItemText>{{ l.name }}</SelectItemText>
                    </SelectItem>
                </SelectViewport>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>