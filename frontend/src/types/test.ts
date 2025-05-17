// TypeScript type definitions for Conference Editor
export type ComponentType = 'wysiwyg' | 'banner' | 'contact';

export interface WysiwygData {
  content: string;
}

export interface BannerData {
  title: string;
  subtitle: string;
  imageUrl: string;
  ctaText: string;
  ctaUrl: string;
}

export interface ContactFormData {
  title: string;
  fields: string[];
  submitText: string;
}

export type ComponentData = WysiwygData | BannerData | ContactFormData;

export interface Component {
  id: string;
  type: ComponentType;
  data: ComponentData;
}

export interface Page {
  id: string;
  title: string;
  components: Component[];
}