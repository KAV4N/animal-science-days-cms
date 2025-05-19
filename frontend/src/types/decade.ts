// File: src/types/decade.ts

import type { ApiResponse, ApiPaginatedResponse } from './common';

export interface Decade {
  decade: number;
  count: number;
}

export type DecadesResponse = ApiResponse<Decade[]>;