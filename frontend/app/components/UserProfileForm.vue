<script setup lang="ts">
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

    const { addToast } = useToast();
    const { user } = useUser();

    const optionalString = z.preprocess(
        (val) => (val === '' ? undefined : val), 
        z.string().min(2, $t('user.form.errors.too_short')).optional()
    );

    const schema = toTypedSchema(z.object({
        firstName: optionalString,
        lastName: optionalString,
        email: z.email($t('user.form.errors.email')),
    }));

    const { defineField, errors, handleSubmit, meta } = useForm({
        validationSchema: schema,
        initialValues: {
            firstName: user.value?.firstName,
            lastName: user.value?.lastName,
            email: user.value?.email
        }
    });

    const [firstName, firstnameProps] = defineField('firstName');
    const [lastName, lastnameProps] = defineField('lastName');
    const [email, emailProps] = defineField('email');

    const onSubmit = handleSubmit(async (values) => {
        try {
            await $fetch('/api/auth/me', {
                method: 'PUT',
                body: values
            });

            addToast({
                title: $t('user.form.success.title'),
                description: $t('user.form.success.desc'),
                type: 'success'
            });
        } catch (err: any) {
            addToast({
                title: $t('user.form.errors.misc'),
                description: err.data?.message,
                type: 'error'
            });
        }
    });

    const logout = async (event: any) => {
        const { addToast } = useToast(); 

        try {
            await $fetch('/api/auth/logout');
            user.value = null;
        } catch (error: any) {
            addToast({
                title: $t('user.logout.errors.misc'),
                description: error.data?.message,
                type: 'error'
            });
        }

        addToast({
            title: $t('logout.title'),
            description: $t('logout.description'),
            type: 'success'
        });
        const localePath = useLocalePath();
        await navigateTo(localePath('login'));
    };

    const deleteUser = async (event: any) => {
        const { addToast } = useToast(); 

        try {
            await $fetch('/api/auth/delete', {
                method: 'DELETE'
            });
            user.value = null;
        } catch (error: any) {
            addToast({
                title: $t('user.delete.errors.misc'),
                description: error.data?.message,
                type: 'error'
            });
        }

        addToast({
            title: $t('delete.title'),
            description: $t('delete.description'),
            type: 'success'
        });
        const localePath = useLocalePath();
        await navigateTo(localePath('index'));
    };
</script>

<template>
    <form @submit="onSubmit" class="max-w-md mx-auto space-y-6 p-6 bg-white rounded-xl shadow-md border border-primary">
        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col space-y-2">
                <Label for="firstname" class="text-sm font-medium text-gray-700">{{ $t('user.form.firstname') }}</Label>
                <input id="firstname" v-model="firstName" v-bind="firstnameProps" type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
                <span class="text-sm text-red-500">{{ errors.firstName }}</span>
            </div>
            <div class="flex flex-col space-y-2">
                <Label for="lastname" class="text-sm font-medium text-gray-700">{{ $t('user.form.lastname') }}</Label>
                <input id="lastname" v-model="lastName" v-bind="lastnameProps" type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
                <span class="text-sm text-red-500">{{ errors.lastName }}</span>
            </div>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="email" class="text-sm font-medium text-gray-700">{{ $t('user.form.email') }}</Label>
            <input id="email" v-model="email" v-bind="emailProps" type="email" required class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.email }}</span>
        </div>

        <button
            type="submit"
            class="w-full rounded-4xl bg-primary text-secondary px-6 py-2.5 hover:text-primary hover:bg-secondary transition-colors duration-400 hover:cursor-pointer"
            :class="!meta.valid 
            ? 'opacity-50 cursor-not-allowed text-gray-500' 
            : 'hover:cursor-pointer'"
            :disabled="!meta.valid"
        >{{ $t('user.form.submit') }}</button>
    </form>
    <div>
        <button
            @click="logout"
            class="max-w-md w-full rounded-4xl bg-accent text-secondary px-6 py-2.5 hover:text-accent hover:bg-secondary transition-colors duration-400 hover:cursor-pointer mb-4"
        >
            {{ $t('user.logout') }}
        </button>
        <button
            @click="deleteUser"
            class="max-w-md w-full rounded-4xl text-red-700 border-2 border-red-700 px-6 py-2.5 hover:text-white hover:bg-red-700 transition-colors duration-400 hover:cursor-pointer"
        >
            {{ $t('user.delete') }}
        </button>
    </div>
</template>