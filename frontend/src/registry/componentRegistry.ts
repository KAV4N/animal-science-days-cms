const componentRegistry = {
    'wysiwyg': {
      edit: () => import('./components/editor/WysiwygEditor.vue'),
      public: () => import('./components/public/WysiwygDisplay.vue')
    },
    'banner': {
      edit: () => import('./components/editor/BannerEditor.vue'),
      public: () => import('./components/public/BannerDisplay.vue')
    },
  }