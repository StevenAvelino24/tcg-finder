<script setup lang="ts">
    const { toasts } = useToast();

    const getBackground = (type: string) => {
        switch (type) {
            case 'success':
                return 'bg-primary text-white'
            case 'info':
                return 'bg-secondary text-black'
            case 'error':
                return 'bg-red-500 text-white'
        
            default:
                return 'bg-white text-black'
        }
    }
</script>

<template>
    <ToastProvider>
        <ToastRoot
            v-for="toast in toasts"
            :key="toast.id"
            class="rounded-lg shadow-xl flex flex-col gap-1 animate-in slide-in-from-right-full p-4"
            :class="getBackground(toast.type)"
        >
            <ToastTitle class="font-bold text-sm">
                {{ toast.title }}
            </ToastTitle>
            <ToastDescription v-if="toast.description" class="text-xs opacity-90">
                {{ toast.description }}
            </ToastDescription>
        </ToastRoot>

        <ToastViewport class="fixed bottom-4 right-4 flex flex-col gap-2 w-[320px] z-100 outline-none" />
    </ToastProvider>
</template>