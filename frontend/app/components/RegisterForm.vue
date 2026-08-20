<script setup lang="ts">
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

    import TextInput from './atoms/TextInput.vue';
    import Button from './atoms/Button.vue';

    const { addToast } = useToast();
    const localePath = useLocalePath();

    const optionalString = z
        .string()
        .min(2, $t('register.form.errors.too_short'))
        .optional()
        .or(z.literal(''))
        .transform((val) => (val === '' ? undefined : val));

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
                <TextInput
                    type="text"
                    label-key="register.form.firstname"
                    id="firstname"
                    :error="errors.firstName"
                    v-model="firstName"
                    v-bind="firstnameProps"
                />
            </div>
            <div class="flex flex-col space-y-2">
                <TextInput
                    type="text"
                    label-key="register.form.lastname"
                    id="lastname"
                    :error="errors.lastName"
                    v-model="lastName"
                    v-bind="lastnameProps"
                />
            </div>
        </div>

        <div class="flex flex-col space-y-2">
            <TextInput
                type="email"
                label-key="register.form.email"
                id="email"
                :error="errors.email"
                v-model="email"
                v-bind="emailProps"
            />
        </div>

        <div class="flex flex-col space-y-2">
            <TextInput
                type="password"
                label-key="register.form.password"
                id="password"
                :error="errors.password"
                v-model="password"
                v-bind="passwordProps"
            />
        </div>

        <div class="flex flex-col space-y-2">
            <TextInput
                type="password"
                label-key="register.form.repeatPassword"
                id="repeatPassword"
                :error="errors.repeatPassword"
                v-model="repeatPassword"
                v-bind="repeatPasswordProps"
            />
        </div>

        <Button
            type="submit"
            variant="primary"
            :disabled="!meta.valid"
        >
            {{ $t('register.form.submit') }}
        </Button>
    </form>
</template>