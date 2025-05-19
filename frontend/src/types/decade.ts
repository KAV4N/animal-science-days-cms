import type { ApiResponse, ApiPaginatedResponse } from './common';

export interface Decade {
  decade: string;
  count: number;
}

export type DecadesResponse = ApiResponse<Decade[]>;