<script setup lang="ts">
    import Button from '~/components/atoms/Button.vue';
    import Dialog from '~/components/molecules/Dialog.vue';
    import Table from '~/components/molecules/Table.vue';
    import TextInput from '~/components/atoms/TextInput.vue';
    import type { Game } from '~/types/game';

    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'

    const { addToast } = useToast();
    const dialogOpened = ref(false);

    const schema = toTypedSchema(z.object({
        name: z.string().min(2, 'Game name is at least 2 characters.'),
    }));

    const { defineField, errors, handleSubmit, meta } = useForm({
        validationSchema: schema,
    });

    const [name, nameProps] = defineField('name');

    definePageMeta({
        middleware: ['admin-only'],
    });

    const { data, refresh } = await useFetch<Game[]>('/api/games/list', {
        method: 'GET'
    })

    const deleteGame = async (gameId: number) => {
        try {
            await $fetch('/api/admin/games/delete/' + gameId, {
                method: 'DELETE'
            });

            addToast({
                title: $t('admin.games.delete.success.title'),
                description: $t('admin.games.delete.success.desc'),
                type: 'success'
            });

            await refresh();
        } catch (err: any) {
            addToast({
                title: $t('admin.games.delete.error.title'),
                description: $t('admin.games.delete.error.desc'),
                type: 'error'
            });
        }
    }

    const addGame = handleSubmit(async (values) => {
        try {
            await $fetch('/api/admin/games/create', {
                method: 'POST',
                body: values
            });

            dialogOpened.value = false;

            addToast({
                title: $t('admin.games.create.success.title'),
                description: $t('admin.games.create.success.desc'),
                type: 'success'
            });

            await refresh();
        } catch (err: any) {
            dialogOpened.value = false;
            addToast({
                title: $t('admin.games.create.error.title'),
                description: $t('admin.games.create.error.desc'),
                type: 'error'
            });
        }
    })
</script>

<template>
    <section class="space-y-8">
        <div class="max-w-md mx-auto text-center mb-16">
            <h1 class="text-3xl font-bold">{{ $t('admin.games.list.title') }}</h1>
        </div>
        <div class="flex flex-row gap-12">
            <div>
                <Dialog
                    title="Add game"
                    v-model="dialogOpened"
                >
                    <template v-slot:trigger>
                        <DialogTrigger as-child>
                            <Button
                                type="button"
                                variant="primary"
                                :disabled="false"
                            >
                                Add game
                            </Button>
                        </DialogTrigger>
                    </template>
                    <template v-slot:content>
                        <form @submit="addGame">
                            <TextInput
                                type="input"
                                label-key="admin.games.create"
                                id="name"
                                :error="errors.name"
                                v-model="name"
                                v-bind="nameProps"
                            />

                            <Button
                                type="submit"
                                variant="primary"
                                :disabled="!meta.valid"
                                classes="mt-6"
                            >
                                {{ $t('admin.games.create.form.submit') }}
                            </Button>
                        </form>
                    </template>
                </Dialog>
            </div>
        </div>
        <Table
            class="mt-16"
            :headings="['id', 'Name', 'Actions']"
        >
            <tr v-for="game in data" :key="game.id" class="border-b border-secondary">
                <th scope="row" class="py-4">{{ game.id }}</th>
                <td>{{ game.name }}</td>
                <td>
                    <Button
                        type="button"
                        variant="accent"
                        :disabled="false"
                        @click="deleteGame(game.id)"
                    >
                        Delete
                    </Button>
                </td>
            </tr>
        </Table>
    </section>
</template>