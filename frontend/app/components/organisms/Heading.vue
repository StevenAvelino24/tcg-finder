<script setup lang="ts">
    import LangSwitcher from '~/components/molecules/LangSwitcher.vue';
    import Link from '~/components/atoms/Link.vue';
    import MobileHeader from '~/components/organisms/MobileHeader.vue';
    import NavBar from '../molecules/NavBar.vue';

    const { isLoggedIn } = useUser();
    const mobileMenuOpened = ref(false);

    const toggleMobileMenu = () => mobileMenuOpened.value = !mobileMenuOpened.value;
</script>

<template>
    <header class="top-0 z-50 w-full border-b border-b-gray-300">
        <div class="hidden md:flex container mx-auto items-center justify-between px-4 py-6">
            <NavBar />
            <div class="flex items-center gap-4">
                <Link
                    v-if="!isLoggedIn"
                    to="login"
                    variant="button"
                    color="primary"
                    text-size="md"
                >
                    {{ $t('header.login') }}
                </Link>
                <Link
                    v-else
                    to="profile"
                    variant="button"
                    color="primary"
                    text-size="md"
                >
                    {{ $t('header.view_profile') }}
                </Link>
                <LangSwitcher />
            </div>
        </div>
        <div class="flex md:hidden container justify-between items-center p-4">
            <span>Logo here</span>
            <button
                type="button"
                @click="toggleMobileMenu"
                :aria-label="$t('a11y.mobile_menu.open')"
                :aria-expanded="mobileMenuOpened"
            >
                <Icon name="tabler:menu-2" style="height: 32px; width: 32px;" />
            </button>
            <MobileHeader v-model="mobileMenuOpened" />
        </div>
    </header>
</template>