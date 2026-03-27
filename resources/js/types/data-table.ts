export type DataTableColumn = {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    headerClass?: string;
};

export type DataTableRow = Record<string, unknown>;
