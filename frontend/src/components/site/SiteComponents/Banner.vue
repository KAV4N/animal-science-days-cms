<template>
  <div class="banner-container">
    <!-- Background Image -->
    <div 
      v-if="data.backgroundImage" 
      class="background-image"
      :style="backgroundImageStyle"
    ></div>
    
    <!-- Shape Divider -->
    <div 
      class="shape-divider" 
      :class="[
        `position-${data.position}`,
        { 'flip-x': data.flipX, 'flip-y': data.flipY }
      ]"
      :style="dividerStyle"
    >
      <svg 
        viewBox="0 0 1200 120"
        width="100%"
        :height="data.height + 'px'"
        preserveAspectRatio="none"
        class="shape-svg"
      >
        <defs>
          <linearGradient 
            id="shapeGradient" 
            :x1="gradientCoords.x1"
            :y1="gradientCoords.y1"
            :x2="gradientCoords.x2"
            :y2="gradientCoords.y2"
            v-if="data.backgroundType === 'gradient'"
          >
            <stop offset="0%" :stop-color="data.gradientColor1" />
            <stop offset="100%" :stop-color="data.gradientColor2" />
          </linearGradient>
          <clipPath :id="`clip-${data.shapeType}`">
            <path :d="currentPath"></path>
          </clipPath>
        </defs>
        
        <!-- Handle shapes with multiple paths (like waves-opacity) -->
        <g v-if="currentShapePaths.length > 1">
          <path 
            v-for="(pathData, index) in currentShapePaths"
            :key="index"
            v-if="!data.shapeImage"
            :d="pathData.d" 
            :fill="fillColor"
            :opacity="pathData.opacity || 1"
          />
          <image
            v-if="data.shapeImage"
            :href="data.shapeImage"
            x="0"
            y="0"
            width="1200"
            height="120"
            preserveAspectRatio="none"
            :clip-path="`url(#clip-${data.shapeType})`"
            :style="{ opacity: (data.shapeImageOpacity || data.imageOpacity) / 100 }"
          />
        </g>
        
        <!-- Handle shapes with single path -->
        <g v-else>
          <path 
            v-if="!data.shapeImage"
            :d="currentPath" 
            :fill="fillColor"
          />
          <image
            v-if="data.shapeImage"
            :href="data.shapeImage"
            x="0"
            y="0"
            width="1200"
            height="120"
            preserveAspectRatio="none"
            :clip-path="`url(#clip-${data.shapeType})`"
            :style="{ opacity: (data.shapeImageOpacity || data.imageOpacity) / 100 }"
          />
        </g>
      </svg>
    </div>

    <!-- Text Overlay -->
    <div 
      v-if="data.enableText && data.textContent" 
      class="text-overlay"
      :style="textStyle"
    >
      {{ data.textContent }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, type PropType, computed } from 'vue';

interface BannerData {
  shapeType: string;
  position: 'top' | 'bottom';
  flipX: boolean;
  flipY: boolean;
  height: number;
  width: number;
  color: string;
  backgroundType: 'solid' | 'gradient';
  gradientColor1: string;
  gradientColor2: string;
  gradientDirection: string;
  opacity: number;
  shapeImage: string;
  backgroundImage: string;
  imageOpacity: number;
  shapeImageOpacity?: number;
  backgroundImageOpacity?: number;
  invert?: boolean;
  enableText?: boolean;
  textContent?: string;
  textSize?: number;
  textColor?: string;
  textWeight?: string;
  textAlign?: string;
  textOpacity?: number;
  textShadow?: boolean;
}

export default defineComponent({
  name: 'BannerPublic',
  props: {
    data: {
      type: Object as PropType<BannerData>,
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
        imageOpacity: 100,
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
    }
  },
  setup(props) {
    // Enhanced shape definitions with complete normal/inverted variants
    const shapes = {
      // Wave shape
      wave: {
        normal: 'M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z',
        inverted: 'M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z'
      },
      
      // Waves with opacity (multiple paths)
      'waves-opacity': [
        {
          d: 'M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z',
          opacity: 0.25
        },
        {
          d: 'M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z',
          opacity: 0.5
        },
        {
          d: 'M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z',
          opacity: 1
        }
      ],
      
      // Curve shape
      curve: {
        normal: 'M0,0V7.23C0,65.52,268.63,112.77,600,112.77S1200,65.52,1200,7.23V0Z',
        inverted: 'M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z'
      },
      
      // Asymmetrical curve
      'curve-asymmetrical': {
        normal: 'M0,0V6c0,21.6,291,111.46,741,110.26,445.39,3.6,459-88.3,459-110.26V0Z',
        inverted: 'M741,116.23C291,117.43,0,27.57,0,6V120H1200V6C1200,27.93,1186.4,119.83,741,116.23Z'
      },
      
      // Triangle shape
      triangle: {
        normal: 'M1200 0L0 0 598.97 114.72 1200 0z',
        inverted: 'M598.97 114.72L0 0 0 120 1200 120 1200 0 598.97 114.72z'
      },
      
      // Asymmetrical triangle
      'triangle-asymmetrical': {
        normal: 'M1200 0L0 0 892.25 114.72 1200 0z',
        inverted: 'M892.25 114.72L0 0 0 120 1200 120 1200 0 892.25 114.72z'
      },
      
      // Arrow shape
      arrow: {
        normal: 'M649.97 0L550.03 0 599.91 54.12 649.97 0z',
        inverted: 'M649.97 0L599.91 54.12 550.03 0 0 0 0 120 1200 120 1200 0 649.97 0z'
      },
      
      // Book shape
      book: {
        normal: 'M1200,0H0V120H281.94C572.9,116.24,602.45,3.86,602.45,3.86h0S632,116.24,923,120h277Z',
        inverted: 'M602.45,3.86h0S572.9,116.24,281.94,120H923C632,116.24,602.45,3.86,602.45,3.86Z'
      },
      
      // Tilt shape (create inverted variant)
      tilt: {
        normal: 'M1200 120L0 16.48 0 0 1200 0 1200 120z',
        inverted: 'M0 0L1200 103.52 1200 120 0 120 0 0z'
      },
      
      // Split shape
      split: {
        normal: 'M0,0V3.6H580.08c11,0,19.92,5.09,19.92,13.2,0-8.14,8.88-13.2,19.92-13.2H1200V0Z',
        inverted: 'M600,16.8c0-8.11-8.88-13.2-19.92-13.2H0V120H1200V3.6H619.92C608.88,3.6,600,8.66,600,16.8Z'
      },
      
      // Legacy shapes - create proper inverted variants
      zigzag: {
        normal: 'M0,96L200,32L400,96L600,32L800,96L1000,32L1200,96L1200,120L0,120Z',
        inverted: 'M0,0L0,24L200,88L400,24L600,88L800,24L1000,88L1200,24L1200,0L0,0Z'
      },
      
      mountains: {
        normal: 'M0,120L200,40L400,80L600,20L800,60L1000,30L1200,40L1200,120L0,120Z',
        inverted: 'M0,0L0,100L200,80L400,40L600,100L800,60L1000,90L1200,80L1200,0L0,0Z'
      },
      
      clouds: {
        normal: 'M0,96C200,32,400,88,600,96C800,32,1000,88,1200,96L1200,120L0,120Z',
        inverted: 'M0,0L0,24C200,88,400,32,600,24C800,88,1000,32,1200,24L1200,0L0,0Z'
      }
    };

    const shouldInvert = computed(() => {
      // Auto-invert logic based on position and explicit invert setting
      if (props.data.invert !== undefined) {
        return props.data.invert;
      }
      // Default behavior: invert for bottom position
      return props.data.position === 'bottom';
    });

    const currentShapePaths = computed(() => {
      const shapeKey = props.data.shapeType;
      const shape = shapes[shapeKey as keyof typeof shapes];
      
      if (!shape) {
        // Fallback to wave if shape not found
        const waveShape = shapes.wave;
        return [{ d: shouldInvert.value ? waveShape.inverted : waveShape.normal }];
      }
      
      // Handle array shapes (like waves-opacity) - these don't invert
      if (Array.isArray(shape)) {
        return shape;
      }
      
      // Handle object shapes with normal/inverted variants
      if (typeof shape === 'object' && 'normal' in shape) {
        return [{ d: shouldInvert.value ? shape.inverted : shape.normal }];
      }
      
      // Handle simple string shapes (shouldn't happen with updated definitions)
      return [{ d: shape as string }];
    });

    const currentPath = computed(() => {
      const paths = currentShapePaths.value;
      return paths.length > 0 ? paths[0].d : '';
    });

    const fillColor = computed(() => {
      if (props.data.backgroundType === 'gradient') {
        return 'url(#shapeGradient)';
      }
      return props.data.color;
    });

    const gradientCoords = computed(() => {
      const direction = props.data.gradientDirection;
      
      switch (direction) {
        case 'to right':
          return { x1: '0%', y1: '0%', x2: '100%', y2: '0%' };
        case 'to left':
          return { x1: '100%', y1: '0%', x2: '0%', y2: '0%' };
        case 'to bottom':
          return { x1: '0%', y1: '0%', x2: '0%', y2: '100%' };
        case 'to top':
          return { x1: '0%', y1: '100%', x2: '0%', y2: '0%' };
        case '45deg':
          return { x1: '0%', y1: '100%', x2: '100%', y2: '0%' };
        case '-45deg':
          return { x1: '100%', y1: '100%', x2: '0%', y2: '0%' };
        default:
          return { x1: '0%', y1: '0%', x2: '100%', y2: '0%' };
      }
    });

    const dividerStyle = computed(() => {
      let transformValue = '';
      
      // Handle manual flip transforms
      if (props.data.flipX && props.data.flipY) {
        transformValue = 'scaleX(-1) scaleY(-1)';
      } else if (props.data.flipX) {
        transformValue = 'scaleX(-1)';
      } else if (props.data.flipY) {
        transformValue = 'scaleY(-1)';
      }

      return {
        '--divider-height': props.data.height + 'px',
        transform: transformValue,
        opacity: props.data.shapeImage ? 1 : props.data.opacity / 100,
        height: props.data.height + 'px'
      };
    });

    const backgroundImageStyle = computed(() => ({
      backgroundImage: props.data.backgroundImage ? `url(${props.data.backgroundImage})` : 'none',
      opacity: (props.data.backgroundImageOpacity || 100) / 100,
      height: props.data.height + 'px',
      width: '100%',
      position: 'absolute' as const,
      top: props.data.position === 'top' ? '0' : 'auto',
      bottom: props.data.position === 'bottom' ? '0' : 'auto',
      left: '0',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      zIndex: -1
    }));

    const textStyle = computed(() => {
      const textShadowValue = props.data.textShadow 
        ? '2px 2px 4px rgba(0, 0, 0, 0.5)' 
        : 'none';

      return {
        fontSize: (props.data.textSize || 32) + 'px',
        color: props.data.textColor || '#ffffff',
        fontWeight: props.data.textWeight || '600',
        textAlign: props.data.textAlign || 'center',
        opacity: (props.data.textOpacity || 100) / 100,
        textShadow: textShadowValue,
        position: 'absolute' as const,
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        width: '90%',
        zIndex: 10,
        pointerEvents: 'none' as const,
        whiteSpace: 'pre-wrap' as const,
        wordBreak: 'break-word' as const
      };
    });

    return {
      currentPath,
      currentShapePaths,
      fillColor,
      gradientCoords,
      dividerStyle,
      backgroundImageStyle,
      textStyle
    };
  }
});
</script>

<style scoped>
.banner-container {
  position: relative;
  width: 100%;
  height: auto;
  min-height: var(--divider-height, 120px);
}

.background-image {
  position: absolute;
}

.shape-divider {
  position: relative;
  width: 100%;
  overflow: hidden;
  line-height: 0;
}

.shape-divider.position-top {
  top: 0;
}

.shape-divider.position-bottom {
  bottom: 0;
}

.shape-divider.flip-x {
  transform: scaleX(-1);
}

.shape-divider.flip-y {
  transform: scaleY(-1);
}

.shape-divider.flip-x.flip-y {
  transform: scaleX(-1) scaleY(-1);
}

.shape-svg {
  position: relative;
  display: block;
  width: 100%;
  height: 100%;
}

.text-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 90%;
  z-index: 10;
  pointer-events: none;
  white-space: pre-wrap;
  word-break: break-word;
}

.banner-container {
  z-index: 1;
}

.background-image {
  z-index: 0;
}

.shape-divider {
  z-index: 1;
}

.text-overlay {
  z-index: 10;
}
</style>