<script setup lang="ts">
    defineProps<{
        to: string;
        label: string;
    }>();
</script>

<template>
    <NavigationMenuItem class="list-none">
        <NavigationMenuLink asChild>
            <NuxtLinkLocale :to="to" class="block p-0 text-lg group w-fit hover:text-accent transition-colors duration-400">
                {{ label }}
                <div class="bg-accent h-0.5 w-0 group-hover:w-full transition-all duration-400"></div>
            </NuxtLinkLocale>
        </NavigationMenuLink>
    </NavigationMenuItem>
</template>