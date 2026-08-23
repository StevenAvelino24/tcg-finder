<script setup lang="ts">
    import Button from '~/components/atoms/Button.vue';
    import Dialog from '~/components/molecules/Dialog.vue';
    import TextInput from '~/components/atoms/TextInput.vue';

    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

    const { addToast } = useToast();
    const dialogOpened = ref(false);

    const schema = toTypedSchema(z.object({
        email: z.email($t('reset_password.form.errors.email')),
    }));

    const { defineField, errors, handleSubmit, meta } = useForm({
        validationSchema: schema,
    });

    const [email, emailProps] = defineField('email');

    const onSubmit = handleSubmit(async (values) => {
        try {
            await $fetch('/api/auth/forgot_password', {
                method: 'POST',
                body: values
            });

            dialogOpened.value = false;

            addToast({
                title: $t('login.forgot_password.success.title'),
                description: $t('login.forgot_password.success.desc'),
                type: 'success'
            });
        } catch (err: any) {
            dialogOpened.value = false;
            addToast({
                title: $t('login.forgot_password.errors.misc'),
                description: err.data?.message,
                type: 'error'
            });
        }
    });
</script>

<template>
    <Dialog
        :title="$t('login.forgot_password.title')"
        :description="$t('login.forgot_password.desc')"
        v-model="dialogOpened"
    >
        <template v-slot:trigger>
            <DialogTrigger as-child>
                <button type="button" class="block p-0 group w-fit transition-colors duration-400 text-accent cursor-pointer">
                    {{ $t('login.forgot_password') }}
                    <div class="h-0.5 w-0 group-hover:w-full transition-all duration-400 bg-accent"/>
                </button>
            </DialogTrigger>
        </template>
        <template v-slot:content>
            <form @submit="onSubmit">
                <TextInput
                    type="email"
                    label-key="login.form.email"
                    id="email"
                    :error="errors.email"
                    v-model="email"
                    v-bind="emailProps"
                />

                <Button
                    type="submit"
                    variant="primary"
                    :disabled="!meta.valid"
                    classes="mt-6"
                >
                    {{ $t('login.forgot_password.form.submit') }}
                </Button>
            </form>
        </template>
    </Dialog>
</template>