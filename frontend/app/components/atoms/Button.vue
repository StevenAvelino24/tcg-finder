<script setup lang="ts">
    defineProps<{
        disabled: boolean,
        variant: 'primary' | 'secondary' | 'accent',
        type: 'button' | 'submit' | 'reset',
        classes?: string
    }>();
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        :class="[
            'w-full rounded-4xl py-2 px-4 transition-colors duration-400 cursor-not-allowed',
            variant === 'primary' && !disabled && 'bg-primary text-secondary hover:bg-secondary hover:text-primary hover:cursor-pointer',
            variant === 'primary' && disabled && 'bg-[#A9C7D3] text-[#FFFFFF]',
            variant === 'secondary' && !disabled && 'bg-secondary text-white hover:bg-white hover:text-secondary hover:cursor-pointer',
            variant === 'secondary' && disabled && 'bg-[#7C8A92] text-[#E5E7EB]',
            variant === 'accent' && !disabled && 'bg-accent text-secondary hover:bg-secondary hover:text-accent hover:cursor-pointer',
            variant === 'accent' && disabled && 'bg-[#F0B6A5] text-[#FFFFFF]',
            classes
        ]"
    >
        <slot />
    </button>
</template>