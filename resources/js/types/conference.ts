export interface Conference {
    id: string;
    name: string;
    date: string;
    location: string;
    imageUrl?: string;
  }
  
  export interface User {
    id: string;
    name: string;
    email: string;
    avatarUrl?: string;
  }
  
  export interface NavigationLink {
    label: string;
    icon?: string;
    url?: string;
    route?: string;
    items?: NavigationSubLink[];
    badge?: string;
    isOpen?: boolean;
  }

  export interface NavigationSubLink {
    label: string;
    url: string;
  }