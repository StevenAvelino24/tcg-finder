<script setup lang="ts">
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

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
        <div class="flex flex-col space-y-2">
            <Label for="email" class="text-sm font-medium text-gray-700">{{ $t('login.form.email') }}</Label>
            <input id="email" v-model="username" v-bind="usernameProps" type="email" required class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.username }}</span>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="password" class="text-sm font-medium text-gray-700">{{ $t('login.form.password') }}</Label>
            <input id="password" v-model="password" v-bind="passwordProps" type="password" required class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.password }}</span>
        </div>

        <button
            type="submit"
            class="w-full bg-primary rounded-4xl text-white py-2 px-4 transition-all"
            :class="!meta.valid 
            ? 'opacity-50 cursor-not-allowed text-gray-500' 
            : 'hover:cursor-pointer hover:opacity-90'"
            :disabled="!meta.valid"
        >{{ $t('login.form.submit') }}</button>
    </form>
</template>