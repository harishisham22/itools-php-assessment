import type { BuildingType } from './building';

export type Tariff = {
    id: number;
    building_type: BuildingType;
    category: string;
    min_kwh: number;
    max_kwh: number | null;
    rate: number;
    is_initial_rate: boolean;
};

export type TariffFormData = {
    building_type: string;
    category: string;
    min_kwh: number | string;
    max_kwh: number | string;
    rate: number | string;
    is_initial_rate: boolean;
};

export type TariffFilters = {
    building_type: string | null;
};
