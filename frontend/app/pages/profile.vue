<script setup lang="ts">
    definePageMeta({
        middleware: 'user-only',
    })

    const { user } = useUser();
</script>

<template>
    <section class="space-y-16">
        <h1 class="text-center text-3xl font-bold">{{ $t('profile.title') }}</h1>
        <div class="flex flex-col lg:flex-row">
            <div class="flex-1 lg:flex-6 lg:border-e-2 lg:space-y-12 text-center">
                <h2 class="text-xl font-bold">{{ $t('profile.user_info') }}</h2>
                <UserProfileForm />
            </div>
            <div class="flex-1 lg:flex-6 lg:space-y-12 text-center">
                <h2 class="text-xl font-bold">{{ $t('profile.shop_info') }}</h2>
                <div v-if="!user?.hasShop">
                    <p class="mb-12">{{ $t('profile.user.no_shop') }}</p>
                    <NuxtLinkLocale class="rounded-4xl bg-primary text-secondary px-6 py-2.5 hover:text-primary hover:bg-secondary transition-colors duration-400" :to="{ name: 'shop-create' }">
                        {{ $t('profile.user.shop_create') }}
                    </NuxtLinkLocale>
                </div>
                <NuxtLinkLocale class="rounded-4xl bg-primary text-secondary px-6 py-2.5 hover:text-primary hover:bg-secondary transition-colors duration-400" :to="{ name: 'shop-portal' }" v-else>
                    {{ $t('profile.user.shop_portal') }}
                </NuxtLinkLocale>
            </div>
        </div>
    </section>
</template>