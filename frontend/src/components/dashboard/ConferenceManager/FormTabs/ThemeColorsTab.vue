<!-- components/dashboard/ConferenceFormTabs/ThemeColorsTab.vue -->
<template>
  <div class="dual-color-picker">
    <div class="color-item">
      <h3>Primary Color</h3>
      <div class="color-display" :style="{ backgroundColor: primaryColor }"></div>
      <TransparentColorPicker 
        v-model="primaryColor" 
        :theme="theme"
        :format="format"
        :custom-class="'primary-color-picker'"
        @update:modelValue="updatePrimaryColor"
        @color-change="onPrimaryColorChange"
      />
    </div>
    
    <div class="color-item">
      <h3>Secondary Color</h3>
      <div class="color-display" :style="{ backgroundColor: secondaryColor }"></div>
      <TransparentColorPicker 
        v-model="secondaryColor" 
        :theme="theme"
        :format="format"
        :custom-class="'secondary-color-picker'"
        @update:modelValue="updateSecondaryColor"
        @color-change="onSecondaryColorChange"
      />
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import TransparentColorPicker from '@/components/dashboard/ConferenceManager/TransparentColorPicker.vue';

export default defineComponent({
  name: 'ThemeColorsTab',
  
  components: {
    TransparentColorPicker
  },
  
  props: {
    theme: {
      type: String,
      default: 'light',
      validator: (value) => ['light', 'dark'].includes(value)
    },
    format: {
      type: String,
      default: 'hex',
      validator: (value) => ['hex', 'rgb', 'hsl'].includes(value)
    }
  },
  
  data() {
    const store = useConferenceStore();
    const conference = store.currentConference || store.getEmptyConference();
    
    return {
      store,
      primaryColor: conference.primaryColor || '#4a90e2',
      secondaryColor: conference.secondaryColor || '#50e3c2'
    };
  },
  
  watch: {
    'store.currentConference': {
      handler(newConference) {
        if (newConference) {
          this.primaryColor = newConference.primaryColor || '#4a90e2';
          this.secondaryColor = newConference.secondaryColor || '#50e3c2';
        }
      },
      immediate: true,
      deep: true
    }
  },
  
  methods: {
    updatePrimaryColor(color) {
      this.primaryColor = color;
      this.store.updateConferenceColors(color, this.secondaryColor);
    },
    
    updateSecondaryColor(color) {
      this.secondaryColor = color;
      this.store.updateConferenceColors(this.primaryColor, color);
    },
    
    onPrimaryColorChange() {
      // Additional actions if needed
    },
    
    onSecondaryColorChange() {
      // Additional actions if needed
    }
  }
});
</script>

<style scoped>
.dual-color-picker {
  display: flex;
  flex-wrap: wrap;
  gap: 0;
  width: 100%;
  max-width: 100%;
  justify-content: center;
  position: relative;
}

.color-item {
  flex: 1;
  min-width: 280px;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 2rem;
}

.color-item:first-child {
  position: relative;
}

.color-item:first-child::after {
  content: '';
  position: absolute;
  right: 0;
  top: 10%;
  height: 80%;
  width: 1px;
  background: #e0e0e0;
}

h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.1rem;
  font-weight: 600;
  text-align: center;
  width: 100%;
}

.color-display {
  width: 100%;
  height: 3rem;
  border-radius: 6px;
  margin-bottom: 1rem;
  border: 1px solid #eaeaea;
}

.color-value {
  margin-top: 0.75rem;
  font-family: monospace;
  font-size: 0.9rem;
  color: #666;
  text-align: center;
}

:deep(.transparent-color-picker) {
  display: flex;
  justify-content: center;
  width: 100%;
}

@media (max-width: 768px) {
  .dual-color-picker {
    flex-direction: column;
    align-items: center;
  }
  
  .color-item {
    margin-bottom: 1.5rem;
    padding: 0 1rem;
    width: 100%;
  }
  
  .color-item:first-child::after {
    content: '';
    position: absolute;
    right: 10%;
    top: auto;
    bottom: -1rem;
    height: 1px;
    width: 80%;
    background: #e0e0e0;
  }
}

@media (max-width: 480px) {
  .color-item {
    min-width: 280px;
    width: 100%;
  }
}
</style>