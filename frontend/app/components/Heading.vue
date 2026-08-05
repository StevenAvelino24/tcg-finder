<script setup lang="ts">
    const { isLoggedIn } = useUser();
</script>

<template>
    <header class="top-0 z-50 w-full border-b border-b-gray-300">
        <div class="container mx-auto flex items-center justify-between px-4 py-6">
            <NavBar />
            <div class="flex items-center gap-4">
                <NuxtLinkLocale v-if="!isLoggedIn" to="login" class="rounded-4xl bg-primary text-secondary px-6 py-2.5 hover:text-primary hover:bg-secondary transition-colors duration-400">
                    {{ $t('header.login') }}
                </NuxtLinkLocale>
                <NuxtLinkLocale class="rounded-4xl bg-primary text-secondary px-6 py-2.5 hover:text-primary hover:bg-secondary transition-colors duration-400" to="profile" v-else>
                    {{ $t('header.view_profile') }}
                </NuxtLinkLocale>
                <LangSwitcher />
            </div>
        </div>
    </header>
</template>