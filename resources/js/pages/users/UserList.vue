<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DataTable from '@/components/data-table/DataTable.vue';
import { index } from '@/routes/users';
import type { DataTableColumn } from '@/types/data-table';
import type { UserListItem } from '@/types/users';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    users: {
        type: Array as () => UserListItem[],
        required: true,
    },
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Listagem de Usuários',
                href: index(),
            },
        ],
    },
});

const userColumns: DataTableColumn[] = [
    {
        key: 'id',
        label: 'ID',
        sortable: true,
        class: 'font-medium',
        headerClass: 'w-24',
    },
    {
        key: 'name',
        label: 'Name',
        sortable: true,
    },
    {
        key: 'email',
        label: 'Email',
        sortable: true,
        class: 'text-muted-foreground',
    },
];
</script>

<template>
    <Head title="Users" />
    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
    >
        <section
            class="rounded-xl border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
        >
            <h2 class="text-lg font-semibold">Registered users</h2>

            <div class="mt-3">
                <DataTable
                    :rows="props.users"
                    :columns="userColumns"
                    caption="Registered users"
                    empty-text="No users found."
                    searchable
                    search-placeholder="Search users..."
                    :page-size="8"
                />
            </div>
        </section>
    </div>
</template>

<style scoped></style>
