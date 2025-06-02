<template>
  <Dialog
    v-model:visible="localVisible"
    header="Edit Shape Divider Banner"
    :style="{ width: '95vw', maxWidth: '1400px', height: '90vh' }"
    :modal="true"
    :maximizable="false"
    :closable="true"
    :breakpoints="{ '640px': '95vw' }"
  >
    <!-- Split Layout Container -->
    <div class="flex flex-col lg:flex-row gap-6 h-full">
      <!-- Left Side - Settings -->
      <div class="flex-1 lg:w-1/2 overflow-y-auto pr-4 space-y-6">
        <!-- Component Name -->
        <div>
          <label for="bannerComponentName" class="block text-sm font-medium mb-2">Component Name</label>
          <InputText
            id="bannerComponentName"
            v-model="localComponentName"
            class="w-full"
            placeholder="Enter component name..."
          />
        </div>

        <!-- Shape Configuration -->
        <Card>
          <template #title>
            <h4 class="text-lg font-semibold">Shape Configuration</h4>
          </template>
          <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-0">
              <!-- Shape Type -->
              <div>
                <label for="shapeType" class="block text-sm font-medium mb-2">Shape Type</label>
                <Select
                  id="shapeType"
                  v-model="localBannerData.shapeType"
                  :options="shapeOptions"
                  optionLabel="label"
                  optionValue="value"
                  placeholder="Select shape"
                  class="w-full"
                />
              </div>

              <!-- Height -->
              <div>
                <label for="height" class="block text-sm font-medium mb-2">Height: {{ localBannerData.height }}px</label>
                <Slider
                  id="height"
                  v-model="localBannerData.height"
                  :min="20"
                  :max="1000"
                  :step="5"
                  class="w-full"
                />
              </div>

              <!-- Density (replaces Width) -->
              <div>
                <label for="density" class="block text-sm font-medium mb-2">Shape Density: {{ localBannerData.density }}%</label>
                <Slider
                  id="density"
                  v-model="localBannerData.density"
                  :min="50"
                  :max="200"
                  :step="10"
                  class="w-full"
                />
              </div>

              <!-- Flip Options -->
              <div class="flex items-center space-x-4">
                <div class="flex items-center">
                  <Checkbox v-model="localBannerData.flipX" inputId="flipX" binary />
                  <label for="flipX" class="ml-2 text-sm">Flip Horizontal</label>
                </div>
                <div class="flex items-center">
                  <Checkbox v-model="localBannerData.flipY" inputId="flipY" binary />
                  <label for="flipY" class="ml-2 text-sm">Flip Vertical</label>
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Images -->
        <Card>
          <template #title>
            <h4 class="text-lg font-semibold">Images</h4>
          </template>
          <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-0">
              <!-- Background Image -->
              <div>
                <label class="block text-sm font-medium mb-2">Background Image</label>
                
                <!-- Background Preview -->
                <div class="mb-4">
                  <div class="w-full h-24 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden">
                    <img
                      v-if="localBannerData.backgroundImage"
                      :src="localBannerData.backgroundImage"
                      alt="Background preview"
                      class="w-full h-full object-cover"
                      @error="handleImageError('background')"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                      <i class="pi pi-image text-2xl"></i>
                    </div>
                  </div>
                </div>

                <div class="flex gap-2 mb-3">
                  <Button
                    label="Browse Gallery"
                    icon="pi pi-images"
                    @click="openMediaManager('background')"
                    outlined
                    size="small"
                  />
                  <Button
                    v-if="localBannerData.backgroundImage"
                    label="Remove"
                    icon="pi pi-times"
                    @click="removeImage('background')"
                    outlined
                    severity="secondary"
                    size="small"
                  />
                </div>

                <InputText
                  v-model="localBannerData.backgroundImage"
                  placeholder="Or enter image URL..."
                  class="w-full mb-3"
                />

                <!-- Background Image Opacity -->
                <div v-if="localBannerData.backgroundImage">
                  <label for="backgroundImageOpacity" class="block text-sm font-medium mb-2">
                    Background Opacity: {{ localBannerData.backgroundImageOpacity }}%
                  </label>
                  <Slider
                    id="backgroundImageOpacity"
                    v-model="localBannerData.backgroundImageOpacity"
                    :min="10"
                    :max="100"
                    :step="5"
                    class="w-full"
                  />
                </div>
              </div>

              <!-- Shape Image -->
              <div>
                <label class="block text-sm font-medium mb-2">Shape Image</label>
                
                <!-- Shape Preview -->
                <div class="mb-4">
                  <div class="w-full h-24 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden">
                    <img
                      v-if="localBannerData.shapeImage"
                      :src="localBannerData.shapeImage"
                      alt="Shape preview"
                      class="w-full h-full object-cover"
                      @error="handleImageError('shape')"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                      <i class="pi pi-star text-2xl"></i>
                    </div>
                  </div>
                </div>

                <div class="flex gap-2 mb-3">
                  <Button
                    label="Browse Gallery"
                    icon="pi pi-images"
                    @click="openMediaManager('shape')"
                    outlined
                    size="small"
                  />
                  <Button
                    v-if="localBannerData.shapeImage"
                    label="Remove"
                    icon="pi pi-times"
                    @click="removeImage('shape')"
                    outlined
                    severity="secondary"
                    size="small"
                  />
                </div>

                <InputText
                  v-model="localBannerData.shapeImage"
                  placeholder="Or enter image URL..."
                  class="w-full mb-3"
                />

                <!-- Shape Image Opacity -->
                <div v-if="localBannerData.shapeImage">
                  <label for="shapeImageOpacity" class="block text-sm font-medium mb-2">
                    Shape Opacity: {{ localBannerData.shapeImageOpacity }}%
                  </label>
                  <Slider
                    id="shapeImageOpacity"
                    v-model="localBannerData.shapeImageOpacity"
                    :min="10"
                    :max="100"
                    :step="5"
                    class="w-full"
                  />
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Colors -->
        <Card>
          <template #title>
            <h4 class="text-lg font-semibold">Colors & Style</h4>
          </template>
          <template #content>
            <div class="p-0 space-y-6">
              <!-- Background Type -->
              <div>
                <label for="backgroundType" class="block text-sm font-medium mb-2">Background Type</label>
                <Select
                  id="backgroundType"
                  v-model="localBannerData.backgroundType"
                  :options="backgroundTypeOptions"
                  optionLabel="label"
                  optionValue="value"
                  placeholder="Select type"
                  class="w-full max-w-xs"
                />
              </div>

              <!-- Single Color Picker -->
              <div v-if="localBannerData.backgroundType === 'solid'">
                <h5 class="text-md font-medium mb-3">Shape Color</h5>
                <div class="color-picker-container">
                  <div class="color-preview" :style="{ backgroundColor: localBannerData.color }"></div>
                  <TransparentColorPicker 
                    v-model="localBannerData.color"
                    theme="light"
                    format="hex"
                    custom-class="shape-color-picker"
                    @update:modelValue="updateShapeColor"
                  />
                </div>
              </div>

              <!-- Dual Color Picker for Gradient -->
              <div v-if="localBannerData.backgroundType === 'gradient'">
                <h5 class="text-md font-medium mb-3">Gradient Colors</h5>
                <DualColorPickerTab
                  :initial-primary-color="localBannerData.gradientColor1"
                  :initial-secondary-color="localBannerData.gradientColor2"
                  theme="light"
                  format="hex"
                  @update:primaryColor="updateGradientColor1"
                  @update:secondaryColor="updateGradientColor2"
                />
                
                <!-- Gradient Direction -->
                <div class="mt-4">
                  <label for="gradientDirection" class="block text-sm font-medium mb-2">Gradient Direction</label>
                  <Select
                    id="gradientDirection"
                    v-model="localBannerData.gradientDirection"
                    :options="gradientDirectionOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select direction"
                    class="w-full max-w-xs"
                  />
                </div>
              </div>

              <!-- Opacity -->
              <div>
                <label for="opacity" class="block text-sm font-medium mb-2">
                  Color Opacity: {{ localBannerData.opacity }}%
                </label>
                <Slider
                  id="opacity"
                  v-model="localBannerData.opacity"
                  :min="10"
                  :max="100"
                  :step="5"
                  class="w-full"
                />
              </div>
            </div>
          </template>
        </Card>

        <!-- Effects -->
        <Card>
          <template #title>
            <h4 class="text-lg font-semibold">Effects</h4>
          </template>
          <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-0">
              <!-- Blur Effect -->
              <div class="flex items-center">
                <Checkbox v-model="localBannerData.blur" inputId="blur" binary />
                <label for="blur" class="ml-2 text-sm font-medium">Enable Blur Effect</label>
              </div>

              <!-- Blur Amount -->
              <div v-if="localBannerData.blur">
                <label for="blurAmount" class="block text-sm font-medium mb-2">
                  Blur Amount: {{ localBannerData.blurAmount }}px
                </label>
                <Slider
                  id="blurAmount"
                  v-model="localBannerData.blurAmount"
                  :min="1"
                  :max="20"
                  :step="1"
                  class="w-full"
                />
              </div>
            </div>
          </template>
        </Card>

        <!-- Published Checkbox -->
        <Card>
          <template #content>
            <div class="flex items-center p-0">
              <Checkbox v-model="localPublished" inputId="bannerPublished" binary />
              <label for="bannerPublished" class="ml-4 text-sm font-medium">
                Publish immediately
              </label>
            </div>
            <p class="text-sm mt-3 ml-8 text-gray-600">Published components are visible on your site</p>
          </template>
        </Card>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
          <Button
            label="Cancel"
            icon="pi pi-times"
            @click="handleCancel"
            outlined
            class="w-full sm:w-auto"
          />
          <Button
            label="Save Changes"
            icon="pi pi-check"
            @click="handleSave"
            autofocus
            class="w-full sm:w-auto"
          />
        </div>
      </div>

      <!-- Right Side - Live Preview -->
      <div class="flex-1 lg:w-1/2 lg:sticky lg:top-0 lg:self-start">
        <Card>
          <template #title>
            <h3 class="text-lg font-semibold">Live Preview</h3>
          </template>
          <template #content>
            <div class="p-0">
              <!-- Dynamic height preview container -->
              <div 
                class="relative bg-gray-100 rounded-lg overflow-hidden border-2 border-gray-200"
                :style="{ 
                  height: Math.max(localBannerData.height, 60) + 'px',
                  minHeight: '60px'
                }"
              >
                <!-- Preview using the actual public component -->
                <BannerPublic :data="previewData" />
              </div>
              <div class="mt-3 text-sm text-gray-600 space-y-1">
                <p><strong>Height:</strong> {{ localBannerData.height }}px</p>
                <p><strong>Density:</strong> {{ localBannerData.density }}%</p>
                <p><strong>Shape:</strong> {{ getShapeLabel(localBannerData.shapeType) }}</p>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- MediaManager Integration -->
    <MediaManager
      v-model:visible="showMediaManager"
      selectionMode="single"
      :allowedMimeTypes="['image/*']"
      @select="handleMediaSelect"
      :conferenceId="conferenceId"
    />
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType, computed } from 'vue';
import MediaManager from '@/components/dashboard/PageEditor/MediaManager.vue';
import BannerPublic from '@/components/site/SiteComponents/Banner.vue';
import TransparentColorPicker from '@/components/dashboard/ConferenceManagement/TransparentColorPicker.vue';
import DualColorPickerTab from '@/components/dashboard/ConferenceManagement/DualColorPickerTab.vue';

interface BannerData {
  shapeType: string;
  position: 'top' | 'bottom';
  flipX: boolean;
  flipY: boolean;
  height: number;
  density: number;
  width?: number; // For backward compatibility
  color: string;
  backgroundType: 'solid' | 'gradient';
  gradientColor1: string;
  gradientColor2: string;
  gradientDirection: string;
  opacity: number;
  blur: boolean;
  blurAmount: number;
  shapeImage: string;
  backgroundImage: string;
  shapeImageOpacity: number;
  backgroundImageOpacity: number;
  imageOpacity?: number; // For backward compatibility
}

export default defineComponent({
  name: 'BannerEditor',
  components: {
    MediaManager,
    BannerPublic,
    TransparentColorPicker,
    DualColorPickerTab
  },
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    componentName: {
      type: String,
      default: ''
    },
    componentData: {
      type: Object as PropType<Partial<BannerData>>,
      default: () => ({
        shapeType: 'wave',
        position: 'bottom',
        flipX: false,
        flipY: false,
        height: 120,
        density: 100,
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
        shapeImageOpacity: 100,
        backgroundImageOpacity: 100
      })
    },
    isPublished: {
      type: Boolean,
      default: false
    },
    conferenceId: {
      type: Number,
      required: true
    }
  },
  emits: ['update:visible', 'save', 'cancel'],
  data() {
    return {
      localComponentName: '',
      localBannerData: {
        shapeType: 'wave',
        position: 'bottom',
        flipX: false,
        flipY: false,
        height: 120,
        density: 100,
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
        shapeImageOpacity: 100,
        backgroundImageOpacity: 100
      } as BannerData,
      localPublished: false,
      localVisible: false,
      showMediaManager: false,
      currentImageType: '' as 'background' | 'shape',
      shapeOptions: [
        { label: 'Wave', value: 'wave' },
        { label: 'Curve', value: 'curve' },
        { label: 'Triangle', value: 'triangle' },
        { label: 'Zigzag', value: 'zigzag' },
        { label: 'Mountains', value: 'mountains' },
        { label: 'Clouds', value: 'clouds' },
        { label: 'Book', value: 'book' },
        { label: 'Arrow', value: 'arrow' }
      ],
      backgroundTypeOptions: [
        { label: 'Solid Color', value: 'solid' },
        { label: 'Gradient', value: 'gradient' }
      ],
      gradientDirectionOptions: [
        { label: 'Left to Right', value: 'to right' },
        { label: 'Right to Left', value: 'to left' },
        { label: 'Top to Bottom', value: 'to bottom' },
        { label: 'Bottom to Top', value: 'to top' },
        { label: 'Diagonal ↗', value: '45deg' },
        { label: 'Diagonal ↖', value: '-45deg' }
      ]
    };
  },
  computed: {
    previewData() {
      // Convert density to width for the BannerPublic component
      return {
        ...this.localBannerData,
        width: this.localBannerData.density,
        imageOpacity: this.localBannerData.shapeImageOpacity // For backward compatibility
      };
    }
  },
  watch: {
    visible: {
      handler(newVal) {
        this.localVisible = newVal;
        if (newVal) {
          this.initializeData();
        }
      },
      immediate: true
    },
    localVisible(newVal) {
      if (!newVal && this.visible) {
        this.handleCancel();
      }
    },
    componentName: {
      handler(newVal) {
        this.localComponentName = newVal;
      },
      immediate: true
    },
    componentData: {
      handler() {
        this.initializeData();
      },
      immediate: true,
      deep: true
    },
    isPublished: {
      handler(newVal) {
        this.localPublished = newVal;
      },
      immediate: true
    }
  },
  methods: {
    initializeData() {
      this.localComponentName = this.componentName;
      
      const bannerData = this.componentData || {};
      
      this.localBannerData = {
        shapeType: bannerData.shapeType || 'wave',
        position: bannerData.position || 'bottom',
        flipX: bannerData.flipX || false,
        flipY: bannerData.flipY || false,
        height: bannerData.height || 120,
        density: bannerData.density || bannerData.width || 100, // Migration support
        color: bannerData.color || '#6366f1',
        backgroundType: bannerData.backgroundType || 'solid',
        gradientColor1: bannerData.gradientColor1 || '#6366f1',
        gradientColor2: bannerData.gradientColor2 || '#8b5cf6',
        gradientDirection: bannerData.gradientDirection || 'to right',
        opacity: bannerData.opacity || 100,
        blur: bannerData.blur || false,
        blurAmount: bannerData.blurAmount || 5,
        shapeImage: bannerData.shapeImage || '',
        backgroundImage: bannerData.backgroundImage || '',
        shapeImageOpacity: bannerData.shapeImageOpacity || bannerData.imageOpacity || 100,
        backgroundImageOpacity: bannerData.backgroundImageOpacity || 100
      };
      
      this.localPublished = this.isPublished;
    },
    
    handleSave() {
      this.$emit('save', {
        name: this.localComponentName,
        data: this.localBannerData,
        isPublished: this.localPublished
      });
    },
    
    handleCancel() {
      this.$emit('cancel');
      this.$emit('update:visible', false);
    },

    openMediaManager(imageType: 'background' | 'shape') {
      this.currentImageType = imageType;
      this.showMediaManager = true;
    },

    removeImage(imageType: 'background' | 'shape') {
      if (imageType === 'background') {
        this.localBannerData.backgroundImage = '';
      } else {
        this.localBannerData.shapeImage = '';
      }
    },

    handleImageError(imageType: 'background' | 'shape') {
      if (imageType === 'background') {
        this.localBannerData.backgroundImage = '';
      } else {
        this.localBannerData.shapeImage = '';
      }
    },

    handleMediaSelect(selectedItem: any) {
      if (selectedItem) {
        const imageUrl = selectedItem.download_url || `/v1/conferences/${this.conferenceId}/media/${selectedItem.id}/download`;
        
        if (this.currentImageType === 'background') {
          this.localBannerData.backgroundImage = imageUrl;
        } else {
          this.localBannerData.shapeImage = imageUrl;
        }
      }
      this.showMediaManager = false;
    },

    getShapeLabel(shapeType: string) {
      const shape = this.shapeOptions.find(option => option.value === shapeType);
      return shape ? shape.label : shapeType;
    },

    // Color picker methods
    updateShapeColor(color: string) {
      this.localBannerData.color = color;
    },

    updateGradientColor1(color: string) {
      this.localBannerData.gradientColor1 = color;
    },

    updateGradientColor2(color: string) {
      this.localBannerData.gradientColor2 = color;
    }
  }
});
</script>

<style scoped>
.color-picker-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.color-preview {
  width: 100%;
  height: 3rem;
  border: 2px solid #d1d5db;
  border-radius: 8px;
  min-width: 200px;
}

/* Custom styling for the color pickers */
:deep(.transparent-color-picker) {
  width: 100%;
  display: flex;
  justify-content: center;
}

:deep(.alwan) {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

/* Ensure proper spacing in the dual color picker */
:deep(.dual-color-picker) {
  width: 100%;
}

:deep(.dual-color-picker .color-item) {
  min-width: 0;
  flex: 1;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .color-picker-container {
    width: 100%;
  }
  
  .color-preview {
    height: 2.5rem;
    min-width: 180px;
  }
}
</style>