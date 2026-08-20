<script setup lang="ts">
    import Select from './atoms/Select.vue'

    const { locale, locales } = useI18n();
    const switchLocalePath = useSwitchLocalePath();

    const availableLocales = computed(() => locales.value.map(locale => ({
        label: locale.name ? locale.name : '',
        value: locale.code ? locale.code : ''
    })));
    const currentLocaleName = computed(() => 
        availableLocales.value.find(l => l.value === locale.value)?.label || locale.value
    )

    async function handleLocaleChange(newLocale: 'fr' | 'en' | 'de' | 'it') {
        const path = switchLocalePath(newLocale);
        if (path) {
            await navigateTo(path);
        }
    }

    watch(locale, (newLocale) => {
        handleLocaleChange(newLocale);
    })
</script>

<template>
    <Select
        v-model="locale"
        :options="availableLocales"
        :placeholder="currentLocaleName"
    />
</template>