<script setup lang="ts">
    import Footing from '~/components/organisms/Footing.vue';
    import Heading from '~/components/organisms/Heading.vue';

</script>

<template>
    <div class="min-h-screen flex flex-col text-primary-text bg-gray-50">
        <Heading />

        <main class="grow">
            <slot />
        </main>

        <Footing />
    </div>
</template>