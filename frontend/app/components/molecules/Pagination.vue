<script setup lang="ts">
    import Select from '../atoms/Select.vue';

    const page = defineModel<number>('page', { required: true });
    const limit = defineModel<number>('limit');

    const props = defineProps<{
        total: number,
        itemsPerPage: number
    }>()
</script>

<template>
    <div class="flex flex-row gap-16 items-center">
        <div class="flex-5 items-start">
            <Select
                :options="[
                    {
                        label: '25',
                        value: 25
                    },
                    {
                        label: '50',
                        value: 50
                    },
                    {
                        label: '100',
                        value: 100
                    }
                ]"
                id="limit"
                v-model="limit"
            />
        </div>
        <div class="flex-7">
            <PaginationRoot
                v-model:page="page"
                :total="total"
                :items-per-page="itemsPerPage"
                :sibling-count="1"
                show-edges
            >
                <PaginationList v-slot="{ items }" class="flex gap-4">
                    <PaginationFirst class="w-9 h-9">
                        <Icon name="tabler:arrow-big-left-lines" />
                    </PaginationFirst>
                    <PaginationPrev class="w-9 h-9">
                        <Icon name="tabler:arrow-big-left" />
                    </PaginationPrev>
                    <template v-for="(page, index) in items">
                        <PaginationListItem
                            v-if="page.type === 'page'"
                            :key="index"
                            :value="page.value"
                        >
                            {{ page.value }}
                        </PaginationListItem>
                        <PaginationEllipsis
                            v-else
                            :key="page.type"
                            :index="index"
                            class="w-9 h-9 flex items-center justify-center"
                        >
                            &#8230;
                        </PaginationEllipsis>
                    </template>
                    <PaginationNext class="w-9 h-9">
                        <Icon name="tabler:arrow-big-right" />
                    </PaginationNext>
                    <PaginationLast class="w-9 h-9">
                        <Icon name="tabler:arrow-big-right-lines" />
                    </PaginationLast>
                </PaginationList>
            </PaginationRoot>
        </div>
    </div>
</template>