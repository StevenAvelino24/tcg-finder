<script setup lang="ts">
    defineProps<{
        triggerClass: string,
        trigger: string,
        title: string,
        description: string
    }>();
</script>

<template>
    <DialogRoot>
        <DialogTrigger :class="triggerClass">
            {{ trigger }}
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay></DialogOverlay>
            <DialogContent>
                <DialogTitle>
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
                <slot name="content"></slot>
                <slot name="button"></slot>
            <DialogClose>
                
            </DialogClose>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>