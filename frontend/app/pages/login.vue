<script setup lang="ts">
    import Link from '~/components/atoms/Link.vue';
</script>

<template>
    <section class="space-y-8">
        <div class="max-w-md mx-auto text-center mb-16">
            <h1 class="text-3xl font-bold">{{ $t('login.form.title') }}</h1>
        </div>
        <LoginForm />
        <Separator class="bg-gray-200 max-w-lg mx-auto h-px my-16" decorative />
        <div class="max-w-md mx-auto flex justify-center">
            <Link
                to="register"
                variant="link"
                color="primary"
                text-size="lg"
            >
                {{ $t('login.to_register') }}
            </Link>
        </div>
    </section>
</template>