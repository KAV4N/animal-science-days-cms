<template name="GalleryPublic">
  <div class="gallery-container p-4" :class="`spacing-${galleryData.spacing}`">
    <!-- Gallery Header -->
    <div v-if="galleryData.title || galleryData.description" class="gallery-header mb-6 text-center">
      <h2 v-if="galleryData.title" class="text-3xl font-bold mb-4">
        {{ galleryData.title }}
      </h2>
      <p v-if="galleryData.description" class="text-gray-600 max-w-2xl mx-auto">
        {{ galleryData.description }}
      </p>
    </div>

    <!-- Gallery Grid -->
    <div 
      v-if="galleryData.images && galleryData.images.length > 0"
      class="gallery-grid"
      :class="[
        `grid-cols-1 sm:grid-cols-2 md:grid-cols-${galleryData.columns}`,
        {
          'gap-2': galleryData.spacing === 'tight',
          'gap-4': galleryData.spacing === 'normal',
          'gap-6': galleryData.spacing === 'loose'
        }
      ]"
    >
      <div
        v-for="(image, index) in galleryData.images"
        :key="index"
        class="gallery-item group cursor-pointer overflow-hidden rounded-lg"
        :class="{
          'aspect-square': galleryData.aspectRatio === 'square',
          'aspect-[4/3]': galleryData.aspectRatio === 'landscape',
          'aspect-[3/4]': galleryData.aspectRatio === 'portrait',
          'aspect-video': galleryData.aspectRatio === 'wide'
        }"
        @click="openLightbox(index)"
      >
        <div class="relative w-full h-full bg-gray-100">
          <img
            :src="image.url"
            :alt="image.alt || image.caption || `Gallery image ${index + 1}`"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            loading="lazy"
          />
          
          <!-- Caption Overlay -->
          <div 
            v-if="galleryData.showCaptions && image.caption"
            class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4"
          >
            <p class="text-white text-sm font-medium">{{ image.caption }}</p>
          </div>
          
          <!-- Hover Overlay -->
          <div 
            v-if="galleryData.enableLightbox"
            class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
          >
            <i class="pi pi-search-plus text-white text-2xl"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <i class="pi pi-images text-6xl text-gray-400 mb-4"></i>
      <h3 class="text-xl font-medium mb-2">No Images Available</h3>
      <p class="text-gray-600">This gallery is currently empty.</p>
    </div>

    <!-- Lightbox Modal -->
    <Dialog 
      v-if="galleryData.enableLightbox"
      v-model:visible="showLightbox" 
      :modal="true"
      :closable="true"
      :style="{ width: '95vw', height: '95vh', maxWidth: '95vw', maxHeight: '95vh' }"
      :breakpoints="{ '640px': '95vw' }"
      :pt="{
        root: { class: 'lightbox-dialog' },
        content: { class: 'p-0 bg-black h-full overflow-hidden' },
        header: { class: 'hidden' }
      }"
    >
      <div v-if="currentImage" class="h-full flex flex-col bg-black">
        <!-- Lightbox Header -->
        <div class="flex justify-between items-center p-4 bg-black/80 text-white">
          <div class="flex items-center gap-4">
            <span class="text-sm">{{ currentImageIndex + 1 }} / {{ galleryData.images.length }}</span>
            <h3 v-if="currentImage.caption" class="font-medium">{{ currentImage.caption }}</h3>
          </div>
          <div class="flex items-center gap-2">
            <Button
              v-if="galleryData.images.length > 1"
              icon="pi pi-chevron-left"
              @click="previousImage"
              :disabled="currentImageIndex === 0"
              text
              class="text-white"
            />
            <Button
              v-if="galleryData.images.length > 1"
              icon="pi pi-chevron-right"
              @click="nextImage"
              :disabled="currentImageIndex === galleryData.images.length - 1"
              text
              class="text-white"
            />
            <Button
              icon="pi pi-times"
              @click="closeLightbox"
              text
              class="text-white"
            />
          </div>
        </div>

        <!-- Lightbox Image -->
        <div class="flex-1 flex items-center justify-center p-4 min-h-0">
          <img
            :src="currentImage.url"
            :alt="currentImage.alt || currentImage.caption"
            class="max-w-full max-h-full w-auto h-auto object-contain"
            style="max-width: calc(100vw - 2rem); max-height: calc(100vh - 8rem);"
          />
        </div>

        <!-- Image Caption -->
        <div v-if="currentImage.caption" class="p-4 bg-black/80 text-white text-center">
          <p>{{ currentImage.caption }}</p>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script lang="ts" name="GalleryPublic">
import { defineComponent, type PropType, onMounted, onUnmounted } from 'vue';

interface GalleryImage {
  id?: string | number;
  url: string;
  thumbnail?: string;
  caption?: string;
  alt?: string;
  filename?: string;
  size?: string;
}

interface GalleryData {
  title?: string;
  description?: string;
  columns?: number;
  spacing?: string;
  aspectRatio?: string;
  showCaptions?: boolean;
  enableLightbox?: boolean;
  images?: GalleryImage[];
}

export default defineComponent({
  name: 'GalleryPublic',
  props: {
    data: {
      type: Object as PropType<GalleryData>,
      required: true
    }
  },
  data() {
    return {
      showLightbox: false,
      currentImageIndex: 0
    };
  },
  computed: {
    galleryData(): Required<GalleryData> {
      // Set defaults and merge with props data
      const defaults: Required<GalleryData> = {
        title: '',
        description: '',
        columns: 3,
        spacing: 'normal',
        aspectRatio: 'square',
        showCaptions: true,
        enableLightbox: true,
        images: []
      };
      
      return {
        ...defaults,
        ...this.data
      };
    },
    currentImage(): GalleryImage | null {
      return this.galleryData.images[this.currentImageIndex] || null;
    }
  },
  methods: {
    openLightbox(index: number) {
      if (this.galleryData.enableLightbox) {
        this.currentImageIndex = index;
        this.showLightbox = true;
      }
    },
    closeLightbox() {
      this.showLightbox = false;
    },
    nextImage() {
      if (this.currentImageIndex < this.galleryData.images.length - 1) {
        this.currentImageIndex++;
      }
    },
    previousImage() {
      if (this.currentImageIndex > 0) {
        this.currentImageIndex--;
      }
    },
    handleKeydown(event: KeyboardEvent) {
      if (this.showLightbox) {
        switch (event.key) {
          case 'Escape':
            this.closeLightbox();
            break;
          case 'ArrowLeft':
            this.previousImage();
            break;
          case 'ArrowRight':
            this.nextImage();
            break;
        }
      }
    }
  },
  mounted() {
    // Keyboard navigation for lightbox
    document.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    // Cleanup event listener when component is destroyed
    document.removeEventListener('keydown', this.handleKeydown);
  }
});
</script>

<style scoped>
.gallery-grid {
  display: grid;
}

.gallery-item {
  position: relative;
  overflow: hidden;
}

.lightbox-dialog .p-dialog-content {
  padding: 0 !important;
  height: 100% !important;
  display: flex !important;
  flex-direction: column !important;
}

.lightbox-dialog .p-dialog {
  max-width: 95vw !important;
  max-height: 95vh !important;
}

/* Responsive grid classes */
@media (min-width: 640px) {
  .sm\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 768px) {
  .md\:grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
  .md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .md\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
  .md\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
  .md\:grid-cols-5 {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }
}
</style>