<template>
  <div class="site-editor-content p-4 ">
    <!-- Content Display -->
    <div 
      v-if="data?.content"
      class="prose prose-lg max-w-none editor-content"
      v-html="sanitizedContent"
    ></div>
    
    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="pi pi-file-edit text-2xl text-gray-400"></i>
      </div>
      <p class="text-gray-500">No content available</p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import DOMPurify from 'dompurify';

interface EditorData {
  content: string;
}

export default defineComponent({
  name: 'SiteEditorComponent',
  props: {
    data: {
      type: Object as PropType<EditorData>,
      default: () => ({ content: '' })
    },
    componentName: {
      type: String,
      default: ''
    },
    conferenceId: {
      type: Number,
      required: false
    }
  },
  computed: {
    sanitizedContent(): string {
      if (!this.data?.content) {
        return '';
      }

      const config = {
        ALLOWED_TAGS: [
          'p', 'br', 'strong', 'em', 'u', 's', 'sub', 'sup',
          'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
          'ul', 'ol', 'li',
          'a', 'img',
          'table', 'thead', 'tbody', 'tr', 'th', 'td',
          'blockquote', 'pre', 'code',
          'div', 'span', 'hr',
          'iframe', 'video', 'source'  // <-- Added here
        ],
        ALLOWED_ATTR: [
          'href', 'target', 'rel',
          'src', 'alt', 'title', 'width', 'height',
          'class', 'style',
          'colspan', 'rowspan',
          'data-*',
          'allow', 'allowfullscreen', 'frameborder', 'scrolling',
          'controls', 'autoplay', 'loop', 'muted', 'poster', 'preload', 'type'  // <-- Added video/source attrs
        ],
        ALLOWED_URI_REGEXP: /^(?:(?:(?:f|ht)tps?|mailto|tel|callto|cid|xmpp|data):|[^a-z]|[a-z+.-]+(?:[^a-z+.-:]|$))/i,
        ADD_TAGS: ['iframe', 'video', 'source'],
        ADD_ATTR: ['controls', 'autoplay', 'loop', 'muted', 'poster', 'preload', 'type']
      };

      return DOMPurify.sanitize(this.data.content, config);
    }
  }
});
</script>

<style scoped>
.site-editor-content {
  word-wrap: break-word;
  overflow-wrap: break-word;
}

/* Enhanced typography styles for better readability */
.editor-content :deep(h1) {
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1.2;
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: #1f2937;
  letter-spacing: -0.025em;
}

.editor-content :deep(h2) {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1.3;
  margin-top: 1.75rem;
  margin-bottom: 0.875rem;
  color: #1f2937;
  letter-spacing: -0.025em;
}

.editor-content :deep(h3) {
  font-size: 1.75rem;
  font-weight: 600;
  line-height: 1.4;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  color: #374151;
}

.editor-content :deep(h4) {
  font-size: 1.5rem;
  font-weight: 600;
  line-height: 1.4;
  margin-top: 1.25rem;
  margin-bottom: 0.625rem;
  color: #374151;
}

.editor-content :deep(h5) {
  font-size: 1.25rem;
  font-weight: 600;
  line-height: 1.5;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  color: #4b5563;
}

.editor-content :deep(h6) {
  font-size: 1.125rem;
  font-weight: 600;
  line-height: 1.5;
  margin-top: 0.875rem;
  margin-bottom: 0.375rem;
  color: #4b5563;
}

.editor-content :deep(p) {
  line-height: 1.75;
  margin-bottom: 1.25rem;
  color: #374151;
  font-size: 1.125rem;
}

.editor-content :deep(a) {
  color: #3b82f6;
  font-size: 1rem;
  text-decoration: underline;
  text-underline-offset: 2px;
  transition: color 0.2s ease;
}

.editor-content :deep(a:hover) {
  color: #1d4ed8;
}

.editor-content :deep(strong) {
  font-weight: 600;
  color: #1f2937;
}

.editor-content :deep(em) {
  font-style: italic;
}

.editor-content :deep(ul) {
  margin-bottom: 1.25rem;
  padding-left: 1.75rem;
  list-style-type: disc;
}

.editor-content :deep(ol) {
  margin-bottom: 1.25rem;
  padding-left: 1.75rem;
  list-style-type: decimal;
}

.editor-content :deep(li) {
  margin-bottom: 0.5rem;
  line-height: 1.75;
  color: #374151;
  font-size: 1.125rem;
}

.editor-content :deep(li > ul),
.editor-content :deep(li > ol) {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}

.editor-content :deep(blockquote) {
  border-left: 4px solid #3b82f6;
  padding-left: 1.5rem;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  font-style: italic;
  color: #6b7280;
  margin: 1.5rem 0;
  background-color: #f8fafc;
  border-radius: 0 0.375rem 0.375rem 0;
}

.editor-content :deep(pre) {
  background-color: #1f2937;
  color: #f9fafb;
  padding: 1.25rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin: 1.5rem 0;
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.875rem;
  line-height: 1.5;
}

.editor-content :deep(code) {
  background-color: #f3f4f6;
  color: #ef4444;
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.875em;
}

.editor-content :deep(pre code) {
  background-color: transparent;
  color: inherit;
  padding: 0;
}

.editor-content :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5rem 0;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.editor-content :deep(th),
.editor-content :deep(td) {
  border: 1px solid #e5e7eb;
  padding: 0.875rem 1rem;
  text-align: left;
  vertical-align: top;
  font-size: 1rem;
  line-height: 1.5;
}

.editor-content :deep(th) {
  background-color: #f9fafb;
  font-weight: 600;
  color: #1f2937;
}

.editor-content :deep(td) {
  color: #374151;
}

.editor-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin: 1.5rem 0;
}

.editor-content :deep(hr) {
  border: none;
  height: 1px;
  background: linear-gradient(to right, transparent, #d1d5db, transparent);
  margin: 2.5rem 0;
}

/* Download links styling */
.editor-content :deep(.download-link) {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  text-decoration: none;
  color: #374151;
  font-weight: 500;
  transition: all 0.2s ease;
  margin: 0.5rem 0;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.editor-content :deep(.download-link:hover) {
  background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
  border-color: #9ca3af;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  text-decoration: none;
  color: #1f2937;
}

.editor-content :deep(.download-icon) {
  font-size: 1.2em;
  color: #6b7280;
}

/* First and last element margins */
.editor-content :deep(> *:first-child) {
  margin-top: 0;
}

.editor-content :deep(> *:last-child) {
  margin-bottom: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .editor-content :deep(h1) {
    font-size: 2rem;
  }
  
  .editor-content :deep(h2) {
    font-size: 1.75rem;
  }
  
  .editor-content :deep(h3) {
    font-size: 1.5rem;
  }
  
  .editor-content :deep(p),
  .editor-content :deep(li) {
    font-size: 1rem;
  }
  
  .editor-content :deep(table) {
    font-size: 0.875rem;
  }
  
  .editor-content :deep(th),
  .editor-content :deep(td) {
    padding: 0.625rem 0.75rem;
  }
}
</style>