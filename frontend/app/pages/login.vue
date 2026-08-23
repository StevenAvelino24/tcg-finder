<script setup lang="ts">
    import type { LocationQueryValue } from 'vue-router';
import Link from '~/components/atoms/Link.vue';

    const route = useRoute();
    const { addToast } = useToast();

    const showToastFromQuery = (queryVariable: LocationQueryValue | LocationQueryValue[] | undefined, message: string) => {
        if (queryVariable) {
            if (queryVariable === 'success') {
                addToast({
                    title: $t(`${message}.success.title`),
                    description: $t(`${message}.success.desc`),
                    type: 'success'
                });
            } else {
                addToast({
                    title: $t(`${message}.error.title`),
                    description: $t(`${message}.error.desc`),
                    type: 'error'
                });
            } 
        }
    }

    showToastFromQuery(route.query.verified, 'login.verified');
    showToastFromQuery(route.query.resent, 'login.resent');
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
        <div class="max-w-md mx-auto text-center">
            <GeneralDialog
                :trigger="$t('login.resend_validation')"
                :title="$t('login.resend_validation.title')"
                :description="$t('login.resend_validation.desc')"
                trigger-class=""
            >
                <template v-slot:content>
                    Test
                </template>
            </GeneralDialog>
        </div>
    </section>
</template>