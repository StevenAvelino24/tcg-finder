<script setup lang="ts">
    import { refDebounced } from '@vueuse/core'
    import Select from '~/components/atoms/Select.vue';
    import TextInput from '~/components/atoms/TextInput.vue';
    import Pagination from '~/components/molecules/Pagination.vue';
    import Table from '~/components/molecules/Table.vue';
    import type { User } from '~/types/user';

    interface UsersResponse {
        users: Array<User>,
        total: number
    }

    definePageMeta({
        middleware: ['admin-only'],
    });

    const page = ref(1);
    const limit = ref(25);
    const searchInput = ref('');
    const search = refDebounced(searchInput, 400);
    const verified = ref(1);

    const { data } = await useFetch<UsersResponse>('/api/admin/users', {
        method: 'GET',
        query: { page, limit, search, verified },
        watch: [page, limit, search, verified]
    })

    watch([search, limit, verified], () => {
        page.value = 1;
    })
</script>

<template>
    <section class="space-y-8">
        <div class="max-w-md mx-auto text-center mb-16">
            <h1 class="text-3xl font-bold">{{ $t('admin.users.list.title') }}</h1>
        </div>
        <div class="flex flex-row gap-12">
            <TextInput
                type="text"
                v-model="searchInput"
                id="search"
                :label-key="$t('admin.users.list.search')"
            />

            <div class="flex flex-col">
                <Select
                :options="[
                    {
                        label: $t('admin.users.list.verified.true'),
                        value: 1
                    },
                    {
                        label: $t('admin.users.list.verified.false'),
                        value: 0
                    }
                ]"
                :label="$t('admin.users.list.verified')"
                id="verified"
                v-model="verified"
            />
            </div>
        </div>
        <Table
            class="mt-16"
            :headings="['id', 'Email', 'First name', 'Last name', 'Shops', 'Is verified', 'Actions']"
        >
            <tr v-for="user in data?.users" :key="user.id" class="border-b border-secondary">
                <th scope="row" class="py-4">{{ user.id }}</th>
                <td>{{ user.email }}</td>
                <td>{{ user.firstName }}</td>
                <td>{{ user.lastName }}</td>
                <td>Shops</td>
                <td>{{ user.isVerified }}</td>
                <td></td>
            </tr>
        </Table>
        <Pagination
            v-if="data?.total && (data?.total > limit)"
            v-model:page="page"
            v-model:limit="limit"
            :total="data?.total"
            :items-per-page="limit"
        />
    </section>
</template>