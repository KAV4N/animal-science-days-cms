export const componentRegistry = {
  'wysiwyg': {
    edit: () => import('@/components/dashboard/PageEditor/EditorComponents/Editor.vue'),
    public: () => import('@/components/site/SiteComponents/Editor.vue')
  },
  'contact': {
    edit: () => import('@/components/dashboard/PageEditor/EditorComponents/Contact.vue'),
    public: () => import('@/components/site/SiteComponents/Contact.vue')
  },
}