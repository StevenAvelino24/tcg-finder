<script setup lang="ts">
    defineOptions({ inheritAttrs: false });

    defineProps<{
        type: string,
        labelKey: string,
        id: string,
        error?: string,
    }>();

    const model = defineModel<string>();
</script>

<template>
    <div class="flex flex-col space-y-2">
        <Label :for="id" class="text-sm font-medium">{{ $t(labelKey) }}</Label>
        <input
            :id="id"
            v-model="model"
            v-bind="$attrs"
            :type="type"
            :class="[
                'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 outline-none transition-all',
                error
                ? 'border-red-500 focus:ring-red-500'
                : 'focus:ring-primary'
            ]" />
        <span class="text-sm text-red-500">{{ error }}</span>
    </div>
</template>