<script setup lang="ts">
    defineProps<{
        options: {label: string, value: string|number}[],
        currentValue?: string
        placeholder?: string
        label?: string
        id?: string
    }>();

    const model = defineModel<string|number>();
</script>

<template>
    <Label v-if="label && id" :for="id" class="pb-2">{{ label }}</Label>
    <SelectRoot :id="id" v-model="model">
        <SelectTrigger class="inline-flex items-center justify-between gap-2 px-3 py-2 text-sm font-medium bg-gray-50 border border-gray-400 rounded-md hover:cursor-pointer">
            <SelectValue :placeholder="placeholder" />
            <Icon name="tabler:chevron-compact-down" class="w-4 h-4" />
        </SelectTrigger>
        <SelectPortal>
            <SelectContent
                class="z-100 bg-gray-50 border-gray-300 rounded-lg shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-100"
                position="popper"
            >
                <SelectViewport class="p-1">
                    <SelectItem
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                        class="relative flex items-center px-8 py-2 text-sm rounded-md cursor-pointer select-none outline-none hover:text-primary"
                    >
                        <SelectItemText>{{ option.label }}</SelectItemText>
                    </SelectItem>
                </SelectViewport>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>