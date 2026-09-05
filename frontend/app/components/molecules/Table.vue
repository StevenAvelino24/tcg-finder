<script setup lang="ts">
    defineProps<{
        headings: Array<string>
    }>();
</script>

<template>
    <table class="w-full text-center">
        <thead class="border-b border-primary">
            <tr>
                <th v-for="heading in headings" :key="heading" scope="col" class="pb-4"> 
                    {{ heading }}
                </th>
            </tr>
        </thead>
        <tbody>
            <slot />
        </tbody>
    </table>
</template>