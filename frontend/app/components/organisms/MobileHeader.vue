<script setup lang="ts">
    import Link from '../atoms/Link.vue';
    import MobileLangSwitcher from '../molecules/MobileLangSwitcher.vue';
    
    const isOpened = defineModel<boolean>();

    const handleEscape = (e: KeyboardEvent) => {
        if (e.key === 'Escape') isOpened.value = false;
    }
    onMounted(() => window.addEventListener('keydown', handleEscape));
    onUnmounted(() => window.removeEventListener('keydown', handleEscape));

    const { isLoggedIn } = useUser();
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpened"
            class="fixed inset-0 bg-black/40 z-40"
            @click="isOpened = false"
        />
    </Transition>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <nav v-if="isOpened" class="fixed p-4 bg-gray-50 z-50 inset-0 flex flex-col" @click="isOpened = false">
            <div class="flex justify-end">
                <button type="button" @click="isOpened = false" :aria-label="$t('a11y.mobile_menu.open')">
                    <Icon name="tabler:x" style="height: 32px; width: 32px;" />
                </button>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center gap-6 mt-16">
                <Link
                    to="/"
                    variant="link"
                    color="secondary"
                    text-size="lg"
                    @click="isOpened = false"
                >
                    {{ $t('navigation.home') }}
                </Link>
                <Link
                    to="search"
                    variant="link"
                    color="secondary"
                    text-size="lg"
                    @click="isOpened = false"
                >
                    {{ $t('navigation.search') }}
                </Link>
            </div>
            <div class="flex flex-col gap-12 mt-16 items-center justify-center w-full border-t pb-8 pt-12 border-gray-300">
                <Link
                    v-if="!isLoggedIn"
                    to="login"
                    variant="button"
                    color="primary"
                    text-size="md"
                    classes="w-full text-center"
                >
                    {{ $t('header.login') }}
                </Link>
                <MobileLangSwitcher />
            </div>
        </nav>
    </Transition>
</template>