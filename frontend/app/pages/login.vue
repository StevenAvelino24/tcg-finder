<template>
    <section class="space-y-8">
        <div class="max-w-md mx-auto text-center">
            <h1 class="text-3xl font-bold">{{ $t('login.form.title') }}</h1>
        </div>
        <LoginForm />
        <Separator class="bg-black max-w-lg mx-auto h-px" decorative />
        <div class="max-w-md mx-auto text-center">
            <NuxtLinkLocale to="register" class="text-primary underline text-md">{{ $t('login.to_register') }}</NuxtLinkLocale>
        </div>
    </section>
</template>