<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import type { DataTableColumn, DataTableRow } from '@/types/data-table';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = withDefaults(
    defineProps<{
        rows: DataTableRow[];
        columns: DataTableColumn[];
        caption?: string;
        emptyText?: string;
        rowKey?: string;
        searchable?: boolean;
        searchPlaceholder?: string;
        pageSize?: number;
    }>(),
    {
        caption: '',
        emptyText: 'No results found.',
        rowKey: 'id',
        searchable: false,
        searchPlaceholder: 'Search...',
        pageSize: 10,
    },
);

const searchTerm = ref('');
const currentPage = ref(1);
const sortKey = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

const searchableColumns = computed(() =>
    props.columns.map((column) => column.key),
);

const filteredRows = computed(() => {
    const term = searchTerm.value.trim().toLowerCase();

    if (term === '') {
        return props.rows;
    }

    return props.rows.filter((row) => {
        return searchableColumns.value.some((columnKey) => {
            const rawValue = readValue(row, columnKey);

            if (rawValue === null || rawValue === undefined) {
                return false;
            }

            return String(rawValue).toLowerCase().includes(term);
        });
    });
});

const sortedRows = computed(() => {
    if (!sortKey.value) {
        return filteredRows.value;
    }

    return [...filteredRows.value].sort((leftRow, rightRow) => {
        const leftValue = readValue(leftRow, sortKey.value as string);
        const rightValue = readValue(rightRow, sortKey.value as string);

        if (leftValue === rightValue) {
            return 0;
        }

        if (leftValue === null || leftValue === undefined) {
            return 1;
        }

        if (rightValue === null || rightValue === undefined) {
            return -1;
        }

        const leftComparable = String(leftValue).toLowerCase();
        const rightComparable = String(rightValue).toLowerCase();

        if (sortDirection.value === 'asc') {
            return leftComparable > rightComparable ? 1 : -1;
        }

        return leftComparable < rightComparable ? 1 : -1;
    });
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(sortedRows.value.length / props.pageSize));
});

const paginatedRows = computed(() => {
    const startIndex = (currentPage.value - 1) * props.pageSize;
    const endIndex = startIndex + props.pageSize;

    return sortedRows.value.slice(startIndex, endIndex);
});

watch(
    () => [searchTerm.value, props.rows.length, props.pageSize],
    () => {
        currentPage.value = 1;
    },
);

watch(totalPages, (pages) => {
    if (currentPage.value > pages) {
        currentPage.value = pages;
    }
});

function toggleSort(column: DataTableColumn): void {
    if (!column.sortable) {
        return;
    }

    if (sortKey.value !== column.key) {
        sortKey.value = column.key;
        sortDirection.value = 'asc';

        return;
    }

    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
}

function readValue(row: DataTableRow, key: string): unknown {
    return key.split('.').reduce<unknown>((value, segment) => {
        if (value === null || value === undefined) {
            return undefined;
        }

        if (typeof value !== 'object') {
            return undefined;
        }

        return (value as Record<string, unknown>)[segment];
    }, row);
}

function rowIdentifier(row: DataTableRow, index: number): string | number {
    const identifier = readValue(row, props.rowKey);

    if (typeof identifier === 'string' || typeof identifier === 'number') {
        return identifier;
    }

    return index;
}

function cellValue(row: DataTableRow, column: DataTableColumn): string {
    const value = readValue(row, column.key);

    if (value === null || value === undefined) {
        return '-';
    }

    return String(value);
}

function goToPreviousPage(): void {
    currentPage.value = Math.max(1, currentPage.value - 1);
}

function goToNextPage(): void {
    currentPage.value = Math.min(totalPages.value, currentPage.value + 1);
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-end gap-5">
            <Button>Adicionar</Button>
            <input
                v-model="searchTerm"
                type="search"
                :placeholder="searchPlaceholder"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background md:max-w-sm"
            />
        </div>

        <Table>
            <TableCaption v-if="caption">{{ caption }}</TableCaption>

            <TableHeader>
                <TableRow>
                    <TableHead
                        v-for="column in columns"
                        :key="column.key"
                        :class="column.headerClass"
                    >
                        <button
                            v-if="column.sortable"
                            type="button"
                            class="inline-flex items-center gap-1 text-left"
                            @click="toggleSort(column)"
                        >
                            {{ column.label }}
                            <span v-if="sortKey === column.key">{{
                                sortDirection === 'asc' ? '↑' : '↓'
                            }}</span>
                        </button>

                        <span v-else>{{ column.label }}</span>
                    </TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableEmpty
                    v-if="paginatedRows.length === 0"
                    :colspan="columns.length"
                >
                    {{ emptyText }}
                </TableEmpty>

                <TableRow
                    v-for="(row, rowIndex) in paginatedRows"
                    v-else
                    :key="rowIdentifier(row, rowIndex)"
                >
                    <TableCell
                        v-for="column in columns"
                        :key="`${String(rowIdentifier(row, rowIndex))}-${column.key}`"
                        :class="column.class"
                    >
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :value="readValue(row, column.key)"
                        >
                            {{ cellValue(row, column) }}
                        </slot>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-muted-foreground">
                Page {{ currentPage }} of {{ totalPages }}
            </p>

            <div class="flex items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="currentPage <= 1"
                    @click="goToPreviousPage"
                >
                    Previous
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="currentPage >= totalPages"
                    @click="goToNextPage"
                >
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>
