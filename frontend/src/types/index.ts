// types/index.ts
export interface Editor {
    id: string;
    name: string;
    email: string;
    role: string;
}

export interface University {
    id: string;
    name: string;
}

export interface Conference {
    id: string;
    name: string;
    slug: string;
    title: string;
    description: string;
    location: string;
    venueDetails?: string;
    startDate: Date | null;
    endDate: Date | null;
    university: University | null;
    isLatest: boolean;
    isPublished: boolean;
    primaryColor: string;
    secondaryColor: string;
    editors: Editor[];
    createdAt: Date;
    updatedAt: Date;
}