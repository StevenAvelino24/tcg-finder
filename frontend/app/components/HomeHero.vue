<template>
    <section class="relative h-[50vh] flex flex-col items-center justify-center">
        <img 
            src="/images/home_hero.jpg" 
            class="absolute inset-0 w-full h-full object-cover"
            alt="TCG Event"
        />
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="container mx-auto max-w-7xl sm:px-6 lg:px-8 z-10 px-4">
            <div class="mb-20">
                <h1 class="font-extrabold text-6xl text-primary mb-6">{{ $t('home.title') }}</h1>
                <h2 class="font-bold text-2xl text-accent">{{ $t('home.subtitle') }}</h2>
            </div>
            <HomeSearch />
        </div>
    </section>
</template>