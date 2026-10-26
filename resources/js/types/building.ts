export type BuildingType = 'commercial' | 'residential';

export type Building = {
    id: number;
    customer_name: string;
    type: BuildingType;
    month: number;
    usability: number;
    state: string;
    bill: number;
};

export type BuildingFormData = {
    customer_name: string;
    type: string;
    month: number | string;
    usability: number | string;
    state: string;
};

export type BuildingFilters = {
    type: string | null;
    state: string | null;
    month: number | null;
    search: string | null;
    sort_by: string | null;
    direction: 'asc' | 'desc';
};

export type SelectOption<T extends string | number = string> = {
    value: T;
    label: string;
};
