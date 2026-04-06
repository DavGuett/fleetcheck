<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import DataTable from '@/components/data-table/DataTable.vue';
import { index, create } from '@/routes/users';
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
        label: 'Nome',
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
            class="rounded-xl border border-border bg-blue-950/60 p-4 dark:border-border"
        >
            <h2 class="text-lg font-semibold">Usuários</h2>

            <div class="mt-3">
                <DataTable
                    :allow-add="true"
                    :createRoute="create.url()"
                    :rows="props.users"
                    :columns="userColumns"
                    empty-text="Nenhum usuário encontrado."
                    searchable
                    search-placeholder="Procurar usuários..."
                    :page-size="8"
                />
            </div>
        </section>
    </div>
</template>

<style scoped></style>
