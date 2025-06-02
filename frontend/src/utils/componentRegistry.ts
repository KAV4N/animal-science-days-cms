// utils/componentRegistry.ts
export interface ComponentDefinition {
  edit: () => Promise<any>;
  public: () => Promise<any>;
  defaultData: any;
  name: string;
  icon: string;
}

export const componentRegistry: Record<string, ComponentDefinition> = {
  'wysiwyg': {
    edit: () => import('@/components/dashboard/PageEditor/EditorComponents/Editor.vue'),
    public: () => import('@/components/site/SiteComponents/Editor.vue'),
    defaultData: {
      content: '<p>Enter content here...</p>'
    },
    name: 'WYSIWYG Editor',
    icon: 'pi pi-file-edit'
  },
  'speaker': {
    edit: () => import('@/components/dashboard/PageEditor/EditorComponents/Speaker.vue'),
    public: () => import('@/components/site/SiteComponents/Speaker.vue'),
    defaultData: {
      name: '',
      title: '',
      company: '',
      email: '',
      phone: '',
      website: '',
      bio: '',
      avatar: '',
      social: {
        linkedin: '',
        twitter: '',
        github: ''
      }
    },
    name: 'Speaker Card',
    icon: 'pi pi-user'
  },
  'gallery': {
    edit: () => import('@/components/dashboard/PageEditor/EditorComponents/Gallery.vue'),
    public: () => import('@/components/site/SiteComponents/Gallery.vue'),
    defaultData: {
      title: 'Photo Gallery',
      description: 'Browse through our collection of images',
      columns: 3,
      spacing: 'normal',
      aspectRatio: 'square',
      showCaptions: true,
      enableLightbox: true,
      images: []
    },
    name: 'Image Gallery',
    icon: 'pi pi-images'
  },
  'banner': {
    edit: () => import('@/components/dashboard/PageEditor/EditorComponents/Banner.vue'),
    public: () => import('@/components/site/SiteComponents/Banner.vue'),
    defaultData: {
      shapeType: 'wave',
      position: 'bottom',
      flipX: false,
      flipY: false,
      height: 120,
      density: 100, // Updated from 'width' to 'density'
      color: '#6366f1',
      backgroundType: 'solid',
      gradientColor1: '#6366f1',
      gradientColor2: '#8b5cf6',
      gradientDirection: 'to right',
      opacity: 100,
      blur: false,
      blurAmount: 5,
      shapeImage: '',
      backgroundImage: '',
      shapeImageOpacity: 100, // Updated from 'imageOpacity'
      backgroundImageOpacity: 100 // New property
    },
    name: 'Shape Divider Banner',
    icon: 'pi pi-bookmark'
  }
};

export function getComponentDefinition(type: string): ComponentDefinition | null {
  return componentRegistry[type] || null;
}

export function getAvailableComponentTypes(): Array<{ label: string; value: string; icon: string }> {
  return Object.entries(componentRegistry).map(([key, definition]) => ({
    label: definition.name,
    value: key,
    icon: definition.icon
  }));
}

export function getComponentIcon(type: string): string {
  const definition = getComponentDefinition(type);
  return definition?.icon || 'pi pi-code';
}

export function getComponentDefaultData(type: string): any {
  const definition = getComponentDefinition(type);
  return definition?.defaultData || {};
}