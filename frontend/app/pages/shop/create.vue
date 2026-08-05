<script setup lang="ts">
    definePageMeta({
        middleware: ['user-only', 'no-shop-user-only'],
    })
</script>

<template>
    <section class="space-y-8">
        <div class="max-w-md mx-auto text-center">
            <h1 class="text-3xl font-bold">{{ $t('shop.create.page.title') }}</h1>
        </div>
        <ShopCreateForm />
    </section>
</template>