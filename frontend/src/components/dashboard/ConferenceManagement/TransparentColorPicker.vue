<template>
    <div class="transparent-color-picker">
      <div ref="colorPickerRef" class="picker-container"></div>
    </div>
  </template>
  
  <script>
  import Alwan from 'alwan';
  import 'alwan/dist/css/alwan.min.css';
  
  export default {
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
        validator: (value) => ['light', 'dark'].includes(value)
      },
      // Color format (hex, rgb, hsl)
      format: {
        type: String,
        default: 'hex',
        validator: (value) => ['hex', 'rgb', 'hsl'].includes(value)
      },
      // Additional CSS classes
      customClass: {
        type: String,
        default: ''
      }
    },
    
    data() {
      return {
        colorPicker: null
      };
    },
    
    mounted() {
      this.initColorPicker();
      
      // Apply transparent background after initialization
      this.$nextTick(() => {
        this.applyTransparentBackground();
      });
    },
    
    methods: {
      initColorPicker() {
        // Create a reference element
        const refElement = document.createElement('div');
        this.$refs.colorPickerRef.appendChild(refElement);
        
        this.colorPicker = new Alwan(refElement, {
          theme: this.theme,
          format: this.format,
          color: this.modelValue,
          inputs: { hex: true, rgb: true,  hsl: false  },
          // Key settings for embedded transparent picker
          opacity: false,                   // Remove alpha/transparency option
          toggle: false,                   // Always visible
          popover: false,                  // Not a popover
          target: this.$refs.colorPickerRef, // Target container
          preset: false,                   // Don't replace with preset button
          classname: 'transparent-picker ' + this.customClass // Custom classes
        });
        
        // Listen for color changes
        this.colorPicker.on('change', (event) => {
          const colorValue = event[this.format];
          this.$emit('update:modelValue', colorValue);
          this.$emit('color-change', event);
        });
      },
      
      applyTransparentBackground() {
        // Make background transparent for all relevant elements
        const elements = this.$el.querySelectorAll('.alwan, .alwan__panel');
        elements.forEach(el => {
          el.style.backgroundColor = 'transparent';
        });
      },
      
      // Public method to set color programmatically
      setColor(color) {
        if (this.colorPicker) {
          this.colorPicker.setColor(color).trigger('change');
        }
      },
      
      // Public method to reset to default color
      reset() {
        if (this.colorPicker) {
          this.colorPicker.reset();
        }
      },
      
      // Clean up
      destroyColorPicker() {
        if (this.colorPicker) {
          this.colorPicker.destroy();
          this.colorPicker = null;
        }
      }
    },
    
    watch: {
      // React to prop changes
      modelValue(newVal) {
        if (this.colorPicker && newVal !== this.colorPicker.getColor()[this.format]) {
          this.colorPicker.setColor(newVal);
        }
      },
      
      theme() {
        // Reinitialize if theme changes
        this.destroyColorPicker();
        this.$nextTick(() => {
          this.initColorPicker();
          this.applyTransparentBackground();
        });
      }
    },
    
    beforeUnmount() {
      this.destroyColorPicker();
    }
  };
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