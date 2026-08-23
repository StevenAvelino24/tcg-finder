<script setup lang="ts">
    const { locale, locales } = useI18n();
    const switchLocalePath = useSwitchLocalePath();

    const availableLocales = computed(() => locales.value);

    async function handleLocaleChange(newLocale: 'fr' | 'en' | 'de' | 'it') {
        if (locale.value === newLocale) {
            return;
        }
        const path = switchLocalePath(newLocale);
        if (path) {
            await navigateTo(path);
        }
    }
</script>

<template>
    <div class="flex flex-row gap-8 justify-center items-center w-full">
        <span
            v-for="availableLocale in availableLocales"
            :key="availableLocale.code"
            @click="handleLocaleChange(availableLocale.code)"
            :class="[
                'flex-1 text-center',
                availableLocale.code === locale && 'text-primary'
            ]"
        >
            {{ availableLocale.code.toUpperCase() }}
        </span>
    </div>
</template>