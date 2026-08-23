<script setup lang="ts">
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

    import TextInput from '~/components/atoms/TextInput.vue';
    import Button from '~/components/atoms/Button.vue';

    const { addToast } = useToast();
    const localePath = useLocalePath();
    const route = useRoute();
    const token = route.query.token as string;

    const schema = toTypedSchema(z.object({
        token: z.string(),
        password: z.string($t('reset_password.form.errors.password.not_blank')).min(8, $t('reset_password.form.errors.password.not_long_enough')),
        repeatPassword: z.string($t('reset_password.form.errors.repeatPassword.not_blank')).min(8, $t('reset_password.form.errors.repeatPassword.not_long_enough')),
    }).refine((data) => data.password === data.repeatPassword, {
        message: $t('reset_password.repeatPassword.not_same'),
        path: ["repeatPassword"],
    }));

    const { defineField, errors, handleSubmit, meta } = useForm({
        validationSchema: schema,
        initialValues: {
            token
        }
    });

    const [password, passwordProps] = defineField('password');
    const [repeatPassword, repeatPasswordProps] = defineField('repeatPassword');

    const onSubmit = handleSubmit(async (values) => {
        try {
            await $fetch('/api/auth/reset_password', {
                method: 'POST',
                body: values
            });

            addToast({
                title: $t('reset_password.form.success.title'),
                description: $t('reset_password.form.success.desc'),
                type: 'success'
            });

            await navigateTo(localePath('login'));
        } catch (err: any) {
            addToast({
                title: $t('reset_password.form.errors.misc'),
                description: err.data?.message,
                type: 'error'
            });
        }
    });
</script>

<template>
    <form @submit="onSubmit" class="max-w-md mx-auto space-y-6 p-6 bg-white rounded-xl shadow-md border border-primary">
        <div class="flex flex-col py-4 pe-2">
            <h1 class="font-bold text-2xl text-center">{{ $t('reset_password.form.title') }}</h1>
        </div>
        <TextInput
            type="password"
            label-key="reset_password.form.password"
            id="password"
            :error="errors.password"
            v-model="password"
            v-bind="passwordProps"
        />

        <TextInput
            type="password"
            label-key="reset_password.form.repeatPassword"
            id="repeatPassword"
            :error="errors.repeatPassword"
            v-model="repeatPassword"
            v-bind="repeatPasswordProps"
        />

        <Button
            type="submit"
            variant="primary"
            :disabled="!meta.valid"
        >
            {{ $t('reset_password.form.submit') }}
        </Button>
    </form>
</template>