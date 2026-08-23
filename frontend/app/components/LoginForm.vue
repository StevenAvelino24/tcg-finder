<script setup lang="ts">
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

    import TextInput from './atoms/TextInput.vue';
    import Button from './atoms/Button.vue';

    const { addToast } = useToast();

    const schema = toTypedSchema(z.object({
        username: z.email($t('login.form.errors.username')),
        password: z.string($t('login.form.errors.password.not_blank')).min(8, $t('login.form.errors.password.not_long_enough')),
    }));

    const { defineField, errors, handleSubmit, meta } = useForm({
        validationSchema: schema,
    });

    const [username, usernameProps] = defineField('username');
    const [password, passwordProps] = defineField('password');

    const onSubmit = handleSubmit(async (values) => {
        try {
            await $fetch('/api/auth/login', {
                method: 'POST',
                body: values
            });

            addToast({
                title: $t('login.form.success.title'),
                description: $t('login.form.success.desc'),
                type: 'success'
            });

            await navigateTo('/');
        }
        catch (err: any) {
            addToast({
                title: $t('login.form.errors.misc'),
                description: err.data?.message,
                type: 'error'
            });
        }
    });
</script>

<template>
    <form @submit="onSubmit" class="max-w-md mx-auto space-y-6 p-6 bg-white rounded-xl shadow-md border border-primary">
        <TextInput
            type="email"
            label-key="login.form.email"
            id="email"
            :error="errors.username"
            v-model="username"
            v-bind="usernameProps"
        />

        <TextInput
            type="password"
            label-key="login.form.password"
            id="password"
            :error="errors.password"
            v-model="password"
            v-bind="passwordProps"
        />

        <Button
            type="submit"
            variant="primary"
            :disabled="!meta.valid"
        >
            {{ $t('login.form.submit') }}
        </Button>
    </form>
</template>