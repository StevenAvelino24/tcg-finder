<template>
    <div class="min-h-screen flex flex-col">
        <Heading />

        <main class="grow">
            <slot />
        </main>

        <Footing />
    </div>
</template>