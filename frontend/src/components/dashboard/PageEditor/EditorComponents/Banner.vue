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

        <!-- Copy/Paste Section -->
        <Card>
          <template #title>
            <h4 class="text-lg font-semibold">Copy & Paste Configuration</h4>
          </template>
          <template #content>
            <div class="flex flex-col sm:flex-row gap-3 p-0">
              <Button
                label="Copy Config"
                icon="pi pi-copy"
                @click="copyConfiguration"
                outlined
                size="small"
                class="flex-1"
                :disabled="!hasValidConfiguration"
              />
              <Button
                label="Paste Config"
                icon="pi pi-clipboard"
                @click="pasteConfiguration"
                outlined
                size="small"
                class="flex-1"
                :disabled="!canPaste"
              />
              <Button
                v-if="copiedConfig"
                label="Clear"
                icon="pi pi-times"
                @click="clearCopiedConfig"
                outlined
                severity="secondary"
                size="small"
              />
            </div>
            <div class="mt-3 text-xs text-gray-600">
              <p v-if="copyMessage" class="text-green-600">{{ copyMessage }}</p>
              <p v-else-if="copiedConfig">Configuration copied and ready to paste</p>
              <p v-else>Copy configuration to reuse in other banner components</p>
            </div>
          </template>
        </Card>

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

              <!-- Invert Shape -->
              <div>
                <div class="flex items-center">
                  <Checkbox v-model="localBannerData.invert" inputId="invert" binary />
                  <label for="invert" class="ml-2 text-sm">Invert Shape</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Automatically adjusts based on position, but can be overridden manually
                </p>
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

        <!-- Text Configuration -->
        <Card>
          <template #title>
            <h4 class="text-lg font-semibold">Text Configuration</h4>
          </template>
          <template #content>
            <div class="p-0 space-y-4">
              <!-- Enable Text -->
              <div class="flex items-center">
                <Checkbox v-model="localBannerData.enableText" inputId="enableText" binary />
                <label for="enableText" class="ml-2 text-sm font-medium">Enable Text Overlay</label>
              </div>

              <!-- Text Settings (shown when text is enabled) -->
              <div v-if="localBannerData.enableText" class="space-y-4">
                <!-- Text Content -->
                <div>
                  <label for="textContent" class="block text-sm font-medium mb-2">Text Content</label>
                  <InputText
                    id="textContent"
                    v-model="localBannerData.textContent"
                    class="w-full"
                    placeholder="Enter your text..."
                  />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Text Size -->
                  <div>
                    <label for="textSize" class="block text-sm font-medium mb-2">
                      Text Size: {{ localBannerData.textSize }}px
                    </label>
                    <Slider
                      id="textSize"
                      v-model="localBannerData.textSize"
                      :min="12"
                      :max="120"
                      :step="2"
                      class="w-full"
                    />
                  </div>

                  <!-- Text Weight -->
                  <div>
                    <label for="textWeight" class="block text-sm font-medium mb-2">Text Weight</label>
                    <Select
                      id="textWeight"
                      v-model="localBannerData.textWeight"
                      :options="textWeightOptions"
                      optionLabel="label"
                      optionValue="value"
                      placeholder="Select weight"
                      class="w-full"
                    />
                  </div>
                </div>

                <!-- Text Color -->
                <div>
                  <label class="block text-sm font-medium mb-2">Text Color</label>
                  
                  <!-- Conference Color Presets for Text -->
                  <div v-if="conferenceColors.length > 0" class="mb-4">
                    <label class="block text-sm font-medium mb-2">Conference Colors</label>
                    <div class="flex gap-2 flex-wrap">
                      <button
                        v-for="color in conferenceColors"
                        :key="`text-${color.value}`"
                        @click="useConferenceTextColor(color.value)"
                        :class="[
                          'w-12 h-12 rounded-lg border-2 transition-all duration-200 hover:scale-110',
                          localBannerData.textColor === color.value ? 'border-gray-800 ring-2 ring-blue-500' : 'border-gray-300 hover:border-gray-500'
                        ]"
                        :style="{ backgroundColor: color.value }"
                        :title="`Use ${color.label} for text`"
                      />
                    </div>
                  </div>

                  <div class="color-picker-container">
                    <div class="color-preview" :style="{ backgroundColor: localBannerData.textColor }"></div>
                    <TransparentColorPicker 
                      v-model="localBannerData.textColor"
                      theme="light"
                      format="hex"
                      custom-class="text-color-picker"
                      @update:modelValue="updateTextColor"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Text Alignment -->
                  <div>
                    <label for="textAlign" class="block text-sm font-medium mb-2">Text Alignment</label>
                    <Select
                      id="textAlign"
                      v-model="localBannerData.textAlign"
                      :options="textAlignOptions"
                      optionLabel="label"
                      optionValue="value"
                      placeholder="Select alignment"
                      class="w-full"
                    />
                  </div>

                  <!-- Text Opacity -->
                  <div>
                    <label for="textOpacity" class="block text-sm font-medium mb-2">
                      Text Opacity: {{ localBannerData.textOpacity }}%
                    </label>
                    <Slider
                      id="textOpacity"
                      v-model="localBannerData.textOpacity"
                      :min="10"
                      :max="100"
                      :step="5"
                      class="w-full"
                    />
                  </div>
                </div>

                <!-- Text Shadow -->
                <div class="flex items-center">
                  <Checkbox v-model="localBannerData.textShadow" inputId="textShadow" binary />
                  <label for="textShadow" class="ml-2 text-sm">Add Text Shadow</label>
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
                
                <!-- Conference Color Presets -->
                <div v-if="conferenceColors.length > 0" class="mb-4">
                  <label class="block text-sm font-medium mb-2">Conference Colors</label>
                  <div class="flex gap-2 flex-wrap">
                    <button
                      v-for="color in conferenceColors"
                      :key="color.value"
                      @click="useConferenceColor(color.value)"
                      :class="[
                        'w-12 h-12 rounded-lg border-2 transition-all duration-200 hover:scale-110',
                        localBannerData.color === color.value ? 'border-gray-800 ring-2 ring-blue-500' : 'border-gray-300 hover:border-gray-500'
                      ]"
                      :style="{ backgroundColor: color.value }"
                      :title="`Use ${color.label}`"
                    />
                  </div>
                </div>

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
                
                <!-- Conference Color Presets for Gradients -->
                <div v-if="conferenceColors.length > 0" class="mb-4">
                  <label class="block text-sm font-medium mb-2">Conference Colors</label>
                  <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                      <label class="block text-xs text-gray-600 mb-1">Primary Color</label>
                      <div class="flex gap-2 flex-wrap">
                        <button
                          v-for="color in conferenceColors"
                          :key="`grad1-${color.value}`"
                          @click="useConferenceGradientColor(color.value, 1)"
                          :class="[
                            'w-8 h-8 rounded border-2 transition-all duration-200 hover:scale-110',
                            localBannerData.gradientColor1 === color.value ? 'border-gray-800 ring-1 ring-blue-500' : 'border-gray-300 hover:border-gray-500'
                          ]"
                          :style="{ backgroundColor: color.value }"
                          :title="`Use ${color.label} for primary`"
                        />
                      </div>
                    </div>
                    <div>
                      <label class="block text-xs text-gray-600 mb-1">Secondary Color</label>
                      <div class="flex gap-2 flex-wrap">
                        <button
                          v-for="color in conferenceColors"
                          :key="`grad2-${color.value}`"
                          @click="useConferenceGradientColor(color.value, 2)"
                          :class="[
                            'w-8 h-8 rounded border-2 transition-all duration-200 hover:scale-110',
                            localBannerData.gradientColor2 === color.value ? 'border-gray-800 ring-1 ring-blue-500' : 'border-gray-300 hover:border-gray-500'
                          ]"
                          :style="{ backgroundColor: color.value }"
                          :title="`Use ${color.label} for secondary`"
                        />
                      </div>
                    </div>
                  </div>
                </div>

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
                <p><strong>Shape:</strong> {{ getShapeLabel(localBannerData.shapeType) }}</p>
                <p><strong>Inverted:</strong> {{ shouldShowInvertStatus ? 'Yes' : 'No' }}</p>
                <p v-if="localBannerData.enableText"><strong>Text:</strong> {{ localBannerData.textContent || 'No text' }}</p>
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

    <!-- Paste Confirmation Dialog -->
    <Dialog
      v-model:visible="showPasteConfirmation"
      header="Paste Configuration"
      :style="{ width: '450px' }"
      :modal="true"
      :closable="true"
    >
      <div class="space-y-4">
        <p>Are you sure you want to paste the copied configuration? This will overwrite all current settings except the component name.</p>
        
        <div class="bg-gray-50 p-3 rounded-lg text-sm">
          <h4 class="font-medium mb-2">Configuration to paste:</h4>
          <ul class="space-y-1 text-gray-600">
            <li>Shape: {{ getShapeLabel(pendingPasteConfig?.shapeType || '') }}</li>
            <li>Height: {{ pendingPasteConfig?.height }}px</li>
            <li>Background: {{ pendingPasteConfig?.backgroundType === 'gradient' ? 'Gradient' : 'Solid Color' }}</li>
            <li v-if="pendingPasteConfig?.enableText">Text: "{{ pendingPasteConfig?.textContent }}"</li>
            <li v-if="pendingPasteConfig?.backgroundImage">Background Image: Yes</li>
            <li v-if="pendingPasteConfig?.shapeImage">Shape Image: Yes</li>
          </ul>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end gap-3">
          <Button
            label="Cancel"
            icon="pi pi-times"
            @click="showPasteConfirmation = false"
            outlined
          />
          <Button
            label="Paste"
            icon="pi pi-check"
            @click="confirmPaste"
            autofocus
          />
        </div>
      </template>
    </Dialog>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType, computed } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
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
  width?: number; // For backward compatibility
  color: string;
  backgroundType: 'solid' | 'gradient';
  gradientColor1: string;
  gradientColor2: string;
  gradientDirection: string;
  opacity: number;
  shapeImage: string;
  backgroundImage: string;
  shapeImageOpacity: number;
  backgroundImageOpacity: number;
  imageOpacity?: number; // For backward compatibility
  invert?: boolean; // New property for manual shape inversion control
  enableText: boolean;
  textContent: string;
  textSize: number;
  textColor: string;
  textWeight: string;
  textAlign: string;
  textOpacity: number;
  textShadow: boolean;
}

interface ConferenceColor {
  label: string;
  value: string;
}

// Key for storing/retrieving configuration from sessionStorage
const BANNER_CONFIG_KEY = 'banner_copied_config';

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
        color: '#6366f1',
        backgroundType: 'solid',
        gradientColor1: '#6366f1',
        gradientColor2: '#8b5cf6',
        gradientDirection: 'to right',
        opacity: 100,
        shapeImage: '',
        backgroundImage: '',
        shapeImageOpacity: 100,
        backgroundImageOpacity: 100,
        invert: false,
        enableText: false,
        textContent: '',
        textSize: 32,
        textColor: '#ffffff',
        textWeight: '600',
        textAlign: 'center',
        textOpacity: 100,
        textShadow: true
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
  setup() {
    const conferenceStore = useConferenceStore();
    return { conferenceStore };
  },
  data() {
    return {
      localComponentName: '',
      localBannerData: {
        shapeType: 'wave',
        position: 'bottom',
        flipX: false,
        flipY: false,
        height: 120,
        color: '#6366f1',
        backgroundType: 'solid',
        gradientColor1: '#6366f1',
        gradientColor2: '#8b5cf6',
        gradientDirection: 'to right',
        opacity: 100,
        shapeImage: '',
        backgroundImage: '',
        shapeImageOpacity: 100,
        backgroundImageOpacity: 100,
        invert: false,
        enableText: false,
        textContent: '',
        textSize: 32,
        textColor: '#ffffff',
        textWeight: '600',
        textAlign: 'center',
        textOpacity: 100,
        textShadow: true
      } as BannerData,
      localPublished: false,
      localVisible: false,
      showMediaManager: false,
      currentImageType: '' as 'background' | 'shape',
      // Copy/Paste related data
      copiedConfig: null as BannerData | null,
      copyMessage: '',
      showPasteConfirmation: false,
      pendingPasteConfig: null as BannerData | null,
      shapeOptions: [
        { label: 'Wave', value: 'wave' },
        { label: 'Waves with Opacity', value: 'waves-opacity' },
        { label: 'Curve', value: 'curve' },
        { label: 'Curve Asymmetrical', value: 'curve-asymmetrical' },
        { label: 'Triangle', value: 'triangle' },
        { label: 'Triangle Asymmetrical', value: 'triangle-asymmetrical' },
        { label: 'Arrow', value: 'arrow' },
        { label: 'Book', value: 'book' },
        { label: 'Tilt', value: 'tilt' },
        { label: 'Split', value: 'split' },
        // Legacy shapes for backward compatibility
        { label: 'Zigzag', value: 'zigzag' },
        { label: 'Mountains', value: 'mountains' },
        { label: 'Clouds', value: 'clouds' }
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
      ],
      textWeightOptions: [
        { label: 'Light', value: '300' },
        { label: 'Normal', value: '400' },
        { label: 'Medium', value: '500' },
        { label: 'Semi Bold', value: '600' },
        { label: 'Bold', value: '700' },
        { label: 'Extra Bold', value: '800' }
      ],
      textAlignOptions: [
        { label: 'Left', value: 'left' },
        { label: 'Center', value: 'center' },
        { label: 'Right', value: 'right' }
      ]
    };
  },
  computed: {
    previewData() {
      return {
        ...this.localBannerData,
        width: 100, // Set to default width since density is removed
        imageOpacity: this.localBannerData.shapeImageOpacity // For backward compatibility
      };
    },
    shouldShowInvertStatus() {
      // Show invert status based on explicit setting or auto-invert logic
      if (this.localBannerData.invert !== undefined) {
        return this.localBannerData.invert;
      }
      return this.localBannerData.position === 'bottom';
    },

    conferenceColors(): ConferenceColor[] {
      const colors: ConferenceColor[] = [];
      
      // Get current conference from store
      const currentConference = this.conferenceStore.conferences.find(c => c.id === this.conferenceId) 
        || this.conferenceStore.latestConference 
        || this.conferenceStore.currentPublicConference;
      
      if (currentConference) {
        // Add primary color if it exists
        if (currentConference.primary_color) {
          colors.push({
            label: 'Primary Color',
            value: currentConference.primary_color
          });
        }
        
        // Add secondary color if it exists
        if (currentConference.secondary_color) {
          colors.push({
            label: 'Secondary Color',
            value: currentConference.secondary_color
          });
        }
      }
      
      return colors;
    },

    // Copy/Paste computed properties
    hasValidConfiguration() {
      // Check if there's a meaningful configuration to copy
      return this.localBannerData.shapeType && this.localBannerData.height > 0;
    },

    canPaste() {
      // Check if there's a valid configuration to paste
      return this.copiedConfig !== null || this.getStoredConfiguration() !== null;
    }
  },
  watch: {
    visible: {
      handler(newVal) {
        this.localVisible = newVal;
        if (newVal) {
          this.initializeData();
          this.loadCopiedConfiguration();
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
        color: bannerData.color || '#6366f1',
        backgroundType: bannerData.backgroundType || 'solid',
        gradientColor1: bannerData.gradientColor1 || '#6366f1',
        gradientColor2: bannerData.gradientColor2 || '#8b5cf6',
        gradientDirection: bannerData.gradientDirection || 'to right',
        opacity: bannerData.opacity || 100,
        shapeImage: bannerData.shapeImage || '',
        backgroundImage: bannerData.backgroundImage || '',
        shapeImageOpacity: bannerData.shapeImageOpacity || bannerData.imageOpacity || 100,
        backgroundImageOpacity: bannerData.backgroundImageOpacity || 100,
        invert: bannerData.invert !== undefined ? bannerData.invert : false,
        enableText: bannerData.enableText || false,
        textContent: bannerData.textContent || '',
        textSize: bannerData.textSize || 32,
        textColor: bannerData.textColor || '#ffffff',
        textWeight: bannerData.textWeight || '600',
        textAlign: bannerData.textAlign || 'center',
        textOpacity: bannerData.textOpacity || 100,
        textShadow: bannerData.textShadow !== undefined ? bannerData.textShadow : true
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
    },

    updateTextColor(color: string) {
      this.localBannerData.textColor = color;
    },

    // Conference color methods
    useConferenceColor(color: string) {
      this.localBannerData.color = color;
    },

    useConferenceGradientColor(color: string, position: 1 | 2) {
      if (position === 1) {
        this.localBannerData.gradientColor1 = color;
      } else {
        this.localBannerData.gradientColor2 = color;
      }
    },

    useConferenceTextColor(color: string) {
      this.localBannerData.textColor = color;
    },

    // Copy/Paste methods
    copyConfiguration() {
      try {
        // Create a deep copy of the current configuration
        const configToCopy = JSON.parse(JSON.stringify(this.localBannerData));
        
        // Store in component state
        this.copiedConfig = configToCopy;
        
        // Store in sessionStorage for persistence across component instances
        this.storeConfiguration(configToCopy);
        
        // Show success message
        this.copyMessage = 'Configuration copied successfully!';
        
        // Clear message after 3 seconds
        setTimeout(() => {
          this.copyMessage = '';
        }, 3000);

      } catch (error) {
        console.error('Failed to copy configuration:', error);
        this.copyMessage = 'Failed to copy configuration';
        
        setTimeout(() => {
          this.copyMessage = '';
        }, 3000);
      }
    },

    pasteConfiguration() {
      const configToPaste = this.copiedConfig || this.getStoredConfiguration();
      
      if (!configToPaste) {
        console.warn('No configuration to paste');
        return;
      }

      // Store the configuration to paste and show confirmation dialog
      this.pendingPasteConfig = configToPaste;
      this.showPasteConfirmation = true;
    },

    confirmPaste() {
      if (!this.pendingPasteConfig) {
        this.showPasteConfirmation = false;
        return;
      }

      try {
        // Apply the configuration (preserving component name)
        const currentName = this.localComponentName;
        this.localBannerData = JSON.parse(JSON.stringify(this.pendingPasteConfig));
        this.localComponentName = currentName;

        
        // Clear the pending config and close dialog
        this.pendingPasteConfig = null;
        this.showPasteConfirmation = false;
        
        // Show success message
        this.copyMessage = 'Configuration pasted successfully!';
        
        setTimeout(() => {
          this.copyMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Failed to paste configuration:', error);
        this.showPasteConfirmation = false;
        
        this.copyMessage = 'Failed to paste configuration';
        
        setTimeout(() => {
          this.copyMessage = '';
        }, 3000);
      }
    },

    clearCopiedConfig() {
      this.copiedConfig = null;
      this.removeStoredConfiguration();
      this.copyMessage = 'Copied configuration cleared';
      
      setTimeout(() => {
        this.copyMessage = '';
      }, 2000);
    },

    loadCopiedConfiguration() {
      // Load any existing copied configuration from storage
      const stored = this.getStoredConfiguration();
      if (stored) {
        this.copiedConfig = stored;
      }
    },

    storeConfiguration(config: BannerData) {
      try {
        // Store configuration with timestamp for potential cleanup
        const configData = {
          config,
          timestamp: Date.now()
        };
        
        // Use in-memory storage since we can't use sessionStorage in artifacts
        if (typeof window !== 'undefined') {
          (window as any).__bannerConfigStore = configData;
        }
      } catch (error) {
        console.error('Failed to store configuration:', error);
      }
    },

    getStoredConfiguration(): BannerData | null {
      try {
        // Retrieve from in-memory storage
        if (typeof window !== 'undefined' && (window as any).__bannerConfigStore) {
          const stored = (window as any).__bannerConfigStore;
          
          // Check if stored data is not too old (24 hours)
          const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
          if (Date.now() - stored.timestamp < maxAge) {
            return stored.config;
          } else {
            // Clean up old data
            this.removeStoredConfiguration();
          }
        }
        
        return null;
      } catch (error) {
        console.error('Failed to retrieve stored configuration:', error);
        return null;
      }
    },

    removeStoredConfiguration() {
      try {
        if (typeof window !== 'undefined') {
          delete (window as any).__bannerConfigStore;
        }
      } catch (error) {
        console.error('Failed to remove stored configuration:', error);
      }
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