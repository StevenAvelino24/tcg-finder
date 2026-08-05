<script setup lang="ts">
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

    const { addToast } = useToast();
    const localePath = useLocalePath();

    const optionalString = z.preprocess(
        (val) => (val === '' ? undefined : val), 
        z.string().min(2, $t('register.form.errors.too_short')).optional()
    );

    const schema = toTypedSchema(z.object({
        firstName: optionalString,
        lastName: optionalString,
        email: z.email($t('register.form.errors.email')),
        password: z.string($t('register.form.errors.password.not_blank')).min(8, $t('register.form.errors.password.not_long_enough')),
        repeatPassword: z.string($t('register.form.errors.repeatPassword.not_blank')).min(8, $t('register.form.errors.repeatPassword.not_long_enough')),
    }).refine((data) => data.password === data.repeatPassword, {
        message: $t('register.repeatPassword.not_same'),
        path: ["repeatPassword"],
    }));

    const { defineField, errors, handleSubmit, meta } = useForm({
        validationSchema: schema,
    });

    const [firstName, firstnameProps] = defineField('firstName');
    const [lastName, lastnameProps] = defineField('lastName');
    const [email, emailProps] = defineField('email');
    const [password, passwordProps] = defineField('password');
    const [repeatPassword, repeatPasswordProps] = defineField('repeatPassword');

    const onSubmit = handleSubmit(async (values) => {
        try {
            await $fetch('/api/auth/register', {
                method: 'POST',
                body: values
            });

            addToast({
                title: $t('register.form.success.title'),
                description: $t('register.form.success.desc'),
                type: 'success'
            });

            await navigateTo(localePath('login'));
        } catch (err: any) {
            addToast({
                title: $t('register.form.errors.misc'),
                description: err.data?.message,
                type: 'error'
            });
        }
    });
</script>

<template>
    <form @submit="onSubmit" class="max-w-md mx-auto space-y-6 p-6 bg-white rounded-xl shadow-md border border-primary">
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col space-y-2">
                <Label for="firstname" class="text-sm font-medium text-gray-700">{{ $t('register.form.firstname') }}</Label>
                <input id="firstname" v-model="firstName" v-bind="firstnameProps" type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
                <span class="text-sm text-red-500">{{ errors.firstName }}</span>
            </div>
            <div class="flex flex-col space-y-2">
                <Label for="lastname" class="text-sm font-medium text-gray-700">{{ $t('register.form.lastname') }}</Label>
                <input id="lastname" v-model="lastName" v-bind="lastnameProps" type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
                <span class="text-sm text-red-500">{{ errors.lastName }}</span>
            </div>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="email" class="text-sm font-medium text-gray-700">{{ $t('register.form.email') }}</Label>
            <input id="email" v-model="email" v-bind="emailProps" type="email" required class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.email }}</span>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="password" class="text-sm font-medium text-gray-700">{{ $t('register.form.password') }}</Label>
            <input id="password" v-model="password" v-bind="passwordProps" type="password" required class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.password }}</span>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="password" class="text-sm font-medium text-gray-700">{{ $t('register.form.repeatPassword') }}</Label>
            <input id="password" v-model="repeatPassword" v-bind="repeatPasswordProps" type="password" required class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.repeatPassword }}</span>
        </div>

        <button
            type="submit"
            class="w-full bg-primary rounded-4xl text-white py-2 px-4 transition-all"
            :class="!meta.valid 
            ? 'opacity-50 cursor-not-allowed text-gray-500' 
            : 'hover:cursor-pointer hover:opacity-90'"
            :disabled="!meta.valid"
        >{{ $t('register.form.submit') }}</button>
    </form>
</template>