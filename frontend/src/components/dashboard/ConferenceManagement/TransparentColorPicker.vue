<template>
  <div class="transparent-color-picker">
    <div ref="colorPickerRef" class="picker-container"></div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, nextTick, watch, onBeforeUnmount } from 'vue';

import AlwanLib from 'alwan';
import 'alwan/dist/css/alwan.min.css';

// Type definitions for Alwan
interface AlwanInstance {
  on(event: string, callback: (event: AlwanColorEvent) => void): void;
  setColor(color: string): AlwanInstance;
  getColor(): { hex: string; rgb: string; hsl: string; [key: string]: string };
  trigger(event: string): AlwanInstance;
  reset(): void;
  destroy(): void;
}

interface AlwanColorEvent {
  hex: string;
  rgb: string;
  hsl: string;
  [key: string]: string;
}

interface AlwanOptions {
  theme?: string;
  format?: string;
  color?: string;
  inputs?: { hex?: boolean; rgb?: boolean; hsl?: boolean };
  opacity?: boolean;
  toggle?: boolean;
  popover?: boolean;
  target?: HTMLElement | null;
  preset?: boolean;
  classname?: string;
}

export default defineComponent({
  name: 'TransparentColorPicker',

  props: {
    // v-model value
    modelValue: {
      type: String,
      default: '#000000'
    },
    // Light or dark theme
    theme: {
      type: String,
      default: 'light',
      validator: (value: string) => ['light', 'dark'].includes(value)
    },
    // Color format (hex, rgb, hsl)
    format: {
      type: String,
      default: 'hex',
      validator: (value: string) => ['hex', 'rgb', 'hsl'].includes(value)
    },
    // Additional CSS classes
    customClass: {
      type: String,
      default: ''
    }
  },

  emits: ['update:modelValue', 'color-change'],

  setup(props, { emit }) {
    const colorPickerRef = ref<HTMLElement | null>(null);
    const colorPicker = ref<AlwanInstance | null>(null);

    const initColorPicker = () => {
      if (!colorPickerRef.value) return;

      // Create a reference element
      const refElement = document.createElement('div');
      colorPickerRef.value.appendChild(refElement);

      // Ensure initial color has # prefix if it's a hex value
      const initialColor = ensureHashPrefix(props.modelValue);

      colorPicker.value = new (AlwanLib as any)(refElement, {
        theme: props.theme,
        format: props.format,
        color: initialColor,
        inputs: { hex: true, rgb: false, hsl: false },
        // Key settings for embedded transparent picker
        opacity: false,                   // Remove alpha/transparency option
        toggle: false,                   // Always visible
        popover: false,                  // Not a popover
        target: colorPickerRef.value,    // Target container
        preset: false,                   // Don't replace with preset button
        classname: 'transparent-picker ' + props.customClass // Custom classes
      });

      // Listen for color changes
      if (colorPicker.value) {
        colorPicker.value.on('change', (event: AlwanColorEvent) => {
          let colorValue = event[props.format];

          // Ensure hex colors always include # prefix
          if (props.format === 'hex' && colorValue && !colorValue.startsWith('#')) {
            colorValue = '#' + colorValue;
          }

          emit('update:modelValue', colorValue);
          emit('color-change', event);
        });
      }
    };

    // Helper method to ensure hex colors start with #
    const ensureHashPrefix = (color: string): string => {
      if (props.format === 'hex' && color && !color.startsWith('#')) {
        return '#' + color;
      }
      return color;
    };

    const applyTransparentBackground = () => {
      if (!colorPickerRef.value) return;

      // Make background transparent for all relevant elements
      const elements = colorPickerRef.value.querySelectorAll('.alwan, .alwan__panel');
      elements.forEach((el: Element) => {
        (el as HTMLElement).style.backgroundColor = 'transparent';
      });
    };

    // Public method to set color programmatically
    const setColor = (color: string) => {
      if (colorPicker.value) {
        const formattedColor = ensureHashPrefix(color);
        colorPicker.value.setColor(formattedColor).trigger('change');
      }
    };

    // Public method to reset to default color
    const reset = () => {
      if (colorPicker.value) {
        colorPicker.value.reset();
      }
    };

    // Clean up
    const destroyColorPicker = () => {
      if (colorPicker.value) {
        colorPicker.value.destroy();
        colorPicker.value = null;
      }
    };

    onMounted(() => {
      initColorPicker();

      // Apply transparent background after initialization
      nextTick(() => {
        applyTransparentBackground();
      });
    });

    // Watch for prop changes
    watch(() => props.modelValue, (newVal) => {
      if (colorPicker.value) {
        const formattedVal = ensureHashPrefix(newVal);
        const currentColor = colorPicker.value.getColor()[props.format];
        const formattedCurrentColor = ensureHashPrefix(currentColor);

        if (formattedVal !== formattedCurrentColor) {
          colorPicker.value.setColor(formattedVal);
        }
      }
    });

    watch(() => props.theme, () => {
      // Reinitialize if theme changes
      destroyColorPicker();
      nextTick(() => {
        initColorPicker();
        applyTransparentBackground();
      });
    });

    onBeforeUnmount(() => {
      destroyColorPicker();
    });

    // Expose methods for parent component access
    return {
      colorPickerRef,
      setColor,
      reset
    };
  }
});
</script>

<style scoped>
.transparent-color-picker {
  display: inline-block;
}

.picker-container {
  /* Minimal styling to allow transparency to work */
  border-radius: 6px;
}

/* Using deep selector to target Alwan's internals */
:deep(.alwan),
:deep(.alwan__panel),
:deep(.alwan__controls),
:deep(.alwan__saturation),
:deep(.alwan__hue) {
  background-color: transparent !important;
}

/* You might want to keep some elements visible */
:deep(.alwan__panel) {
  border: 1px solid #eee;
}

:deep(.transparent-picker) {
  background-color: transparent !important;
}
</style>
