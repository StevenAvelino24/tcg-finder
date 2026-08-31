<script setup lang="ts">
    import Footing from '~/components/organisms/Footing.vue';
    import Heading from '~/components/organisms/Heading.vue';

</script>

<template>
    <div class="min-h-screen flex flex-col tracking-wide text-primary-text bg-gray-50">
        <Heading />

        <main class="grow">
            <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8 max-w-7xl">
                <slot />
            </div>
        </main>

        <Footing />
    </div>
</template>