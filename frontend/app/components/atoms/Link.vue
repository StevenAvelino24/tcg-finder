<script setup lang="ts">
    defineProps<{
        to: string,
        variant: 'button' | 'link',
        color: 'primary' | 'secondary' | 'accent'
        textSize: 'sm' | 'md' | 'lg'
    }>();
</script>

<template>
    <NuxtLinkLocale
        v-if="variant === 'button'"
        :to="to"
        :class="[
            'rounded-4xl py-2 px-4 transition-colors duration-400 hover:cursor-pointer',
            color === 'primary' && 'bg-primary text-secondary hover:bg-secondary hover:text-primary',
            color === 'secondary' && 'bg-secondary text-white hover:bg-white hover:text-secondary',
            color === 'accent' && 'bg-accent text-secondary hover:bg-secondary hover:text-accent',
        ]"
    >
        <slot />
    </NuxtLinkLocale>
    <NuxtLinkLocale
        v-else-if="variant === 'link'"
        :to="to"
        :class="[
            'block p-0 group w-fit transition-colors duration-400',
            color === 'primary' && 'text-primary',
            color === 'secondary' && 'text-secondary',
            color === 'accent' && 'text-accent',
            `text-${textSize}`
        ]"
    >
        <slot />
        <div
            :class="[
                'h-0.5 w-0 group-hover:w-full transition-all duration-400',
                color === 'primary' && 'bg-primary',
                color === 'secondary' && 'bg-secondary',
                color === 'accent' && 'bg-accent'
             ]"
        />
    </NuxtLinkLocale>
</template>