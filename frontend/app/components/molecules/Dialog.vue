<script setup lang="ts">
    defineProps<{
        title: string,
        description?: string
    }>();

    const opened = defineModel<boolean>();
</script>

<template>
    <DialogRoot v-model:open="opened"">
        <slot name="trigger"></slot>
        <DialogPortal>
            <DialogOverlay class="bg-gray-600 opacity-60 data-[state=open]:animate-overlayShow fixed inset-0 z-200"></DialogOverlay>
            <DialogContent class="data-[state=open]:animate-contentShow fixed top-[50%] left-[50%] max-h-[85vh] w-[90vw] max-w-112.5 translate-x-[-50%] translate-y-[-50%] rounded-md bg-white p-8 focus:outline-none z-300">
                <DialogTitle class="font-semibold text-xl mb-2">
                    {{ title }}
                </DialogTitle>
                <DialogDescription v-if="description" class="mb-6 text-sm">
                    {{ description }}
                </DialogDescription>
                <slot name="content"></slot>
            <DialogClose>
                
            </DialogClose>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>