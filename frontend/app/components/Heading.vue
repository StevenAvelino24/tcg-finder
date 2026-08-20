<script setup lang="ts">
    import Link from './atoms/Link.vue';

    const { isLoggedIn } = useUser();
</script>

<template>
    <header class="top-0 z-50 w-full border-b border-b-gray-300">
        <div class="container mx-auto flex items-center justify-between px-4 py-6">
            <NavBar />
            <div class="flex items-center gap-4">
                <Link
                    v-if="!isLoggedIn"
                    to="login"
                    variant="button"
                    color="primary"
                    text-size="md"
                >
                    {{ $t('header.login') }}
                </Link>
                <Link
                    v-else
                    to="profile"
                    variant="button"
                    color="primary"
                    text-size="md"
                >
                    {{ $t('header.view_profile') }}
                </Link>
                <LangSwitcher />
            </div>
        </div>
    </header>
</template>