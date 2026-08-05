<template>
    <NavigationMenuRoot class="flex items-center gap-8">
        <NavLink to="/" :label="$t('navigation.home')" />
        <NavLink to="search" :label="$t('navigation.search')" />
    </NavigationMenuRoot>
</template>

