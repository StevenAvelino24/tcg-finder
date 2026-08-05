<script setup lang="ts">
    import { useForm } from 'vee-validate';
    import { toTypedSchema } from '@vee-validate/zod';
    import * as z from 'zod';

    const { addToast } = useToast();
    const localePath = useLocalePath();

    const optionalString = z.preprocess(
        (val) => (val === '' ? undefined : val), 
        z.string().min(2, $t('shop.create.errors.too_short')).optional()
    );

    const schema = toTypedSchema(z.object({
        title: z.string($t('shop.create.errors.title')).max(150, $t('shop.create.errors.title.too_long')),
        address: z.string($t('shop.create.errors.address')).max(150, $t('shop.create.errors.address.too_long')),
        city: z.string($t('shop.create.errors.city')).max(80, $t('shop.create.errors.city.too_long')),
        state: z.string($t('shop.create.errors.state')).max(40, $t('shop.create.errors.state.too_long')),
        zipcode: z.number($t('shop.create.errors.zipcode')).positive($t('shop.create.errors.zipcode.positive')),
        openingHours: optionalString,
        phone: optionalString,
        email: z.email($t('shop.create.email')).optional(),
        selling: z.boolean(),
        description: optionalString
    }));

    const { defineField, errors, handleSubmit, meta } = useForm({
        validationSchema: schema,
    });

    const [title, titleProps] = defineField('title');
    const [address, addressProps] = defineField('address');
    const [city, cityProps] = defineField('city');
    const [state, stateProps] = defineField('state');
    const [zipcode, zipcodeProps] = defineField('zipcode');
    const [openingHours, openingHoursProps] = defineField('openingHours');
    const [phone, phoneProps] = defineField('phone');
    const [email, emailProps] = defineField('email');
    const [selling, sellingProps] = defineField('selling');
    const [description, descriptionProps] = defineField('description');

    const onSubmit = handleSubmit(async (values) => {
        try {
            await $fetch('/api/shop/create', {
                method: 'POST',
                body: values
            });

            addToast({
                title: $t('shop.create.success.title'),
                description: $t('shop.create.success.desc'),
                type: 'success'
            });

            await navigateTo(localePath('shop-portal'));
        } catch (err: any) {
            addToast({
                title: $t('shop.create.errors.misc'),
                description: err.data?.message,
                type: 'error'
            });
        }
    });
</script>

<template>
    <form @submit="onSubmit" class="max-w-xl mx-auto space-y-6 p-6 bg-white rounded-xl shadow-md border border-primary">
        <div class="flex flex-col space-y-2">
            <Label for="title" class="text-sm font-medium text-gray-700">{{ $t('shop.create.title') }}</Label>
            <input id="title" v-model="title" v-bind="titleProps" required type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.title }}</span>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="address" class="text-sm font-medium text-gray-700">{{ $t('shop.create.address') }}</Label>
            <input id="address" v-model="address" v-bind="addressProps" required class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.address }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="flex flex-col space-y-2">
                <Label for="zipcode" class="text-sm font-medium text-gray-700">{{ $t('shop.create.zipcode') }}</Label>
                <input id="zipcode" v-model="zipcode" v-bind="zipcodeProps" required type="number" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all no-spinner" />
                <span class="text-sm text-red-500">{{ errors.zipcode }}</span>
            </div>
            <div class="flex flex-col space-y-2">
                <Label for="city" class="text-sm font-medium text-gray-700">{{ $t('shop.create.city') }}</Label>
                <input id="city" v-model="city" v-bind="cityProps" required type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
                <span class="text-sm text-red-500">{{ errors.city }}</span>
            </div>
            <div class="flex flex-col space-y-2">
                <Label for="state" class="text-sm font-medium text-gray-700">{{ $t('shop.create.state') }}</Label>
                <input id="state" v-model="state" v-bind="stateProps" required type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
                <span class="text-sm text-red-500">{{ errors.state }}</span>
            </div>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="openingHours" class="text-sm font-medium text-gray-700">{{ $t('shop.create.openingHours') }}</Label>
            <textarea id="openingHours" v-model="openingHours" v-bind="openingHoursProps" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.openingHours }}</span>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="phone" class="text-sm font-medium text-gray-700">{{ $t('shop.create.phone') }}</Label>
            <input id="phone" v-model="phone" v-bind="phoneProps" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.phone }}</span>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="email" class="text-sm font-medium text-gray-700">{{ $t('shop.create.email') }}</Label>
            <input id="email" v-model="email" v-bind="emailProps" type="email" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.email }}</span>
        </div>

        <div class="flex flex-col space-y-2">
            <label class="flex flex-row gap-2.5 items-center">
                <CheckboxRoot v-model="selling" v-bind="sellingProps" class="h-5 w-5 flex appearance-none items-center justify-center rounded-md shadow-sm bg-primary border outline-none">
                    <CheckboxIndicator>
                        <Icon name="tabler:check" class="w-6 h-6 text-white" />
                    </CheckboxIndicator>
                </CheckboxRoot>
                <span class="select-none text-xm text-gray-700">{{ $t('shop.create.selling') }}</span>
            </label>
        </div>

        <div class="flex flex-col space-y-2">
            <Label for="description" class="text-sm font-medium text-gray-700">{{ $t('shop.create.description') }}</Label>
            <textarea id="description" v-model="description" v-bind="descriptionProps" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary outline-none transition-all" />
            <span class="text-sm text-red-500">{{ errors.description }}</span>
        </div>

        <button
            type="submit"
            class="w-full bg-primary rounded-4xl text-white py-2 px-4 transition-all mt-6"
            :class="!meta.valid 
            ? 'opacity-50 cursor-not-allowed text-gray-500' 
            : 'hover:cursor-pointer hover:opacity-90'"
            :disabled="!meta.valid"
        >{{ $t('shop.create.submit') }}</button>
    </form>
</template>