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
        viewBox="0 0 1200 320"
        :width="(data.width || data.density || 100) + '%'"
        :height="data.height + 'px'"
        preserveAspectRatio="none"
        class="shape-svg"
      >
        <defs>
          <linearGradient 
            id="shapeGradient" 
            :gradientTransform="gradientTransform"
            v-if="data.backgroundType === 'gradient'"
          >
            <stop offset="0%" :stop-color="data.gradientColor1" />
            <stop offset="100%" :stop-color="data.gradientColor2" />
          </linearGradient>
          <clipPath :id="`clip-${data.shapeType}`">
            <path :d="currentPath"></path>
          </clipPath>
        </defs>
        <path 
          v-if="!data.shapeImage"
          :d="currentPath" 
          :fill="fillColor"
        ></path>
        <image
          v-if="data.shapeImage"
          :href="data.shapeImage"
          x="0"
          y="0"
          width="1200"
          height="320"
          preserveAspectRatio="none"
          :clip-path="`url(#clip-${data.shapeType})`"
          :style="{ opacity: (data.shapeImageOpacity || data.imageOpacity) / 100 }"
        />
      </svg>
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
  density?: number;
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
  imageOpacity: number;
  shapeImageOpacity?: number;
  backgroundImageOpacity?: number;
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
        imageOpacity: 100,
        shapeImageOpacity: 100,
        backgroundImageOpacity: 100
      })
    }
  },
  setup(props) {
    const shapes = {
      wave: 'M0,192L48,208C96,224,192,256,288,256C384,256,480,224,576,208C672,192,768,192,864,208C960,224,1056,256,1152,256C1200,256,1248,224,1296,208L1344,192L1344,320L1296,320C1248,320,1200,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z',
      curve: 'M0,320L1200,128L1200,320L0,320Z',
      triangle: 'M0,320L600,128L1200,320L0,320Z',
      zigzag: 'M0,192L200,256L400,192L600,256L800,192L1000,256L1200,192L1200,320L0,320Z',
      mountains: 'M0,320L200,240L400,280L600,200L800,260L1000,180L1200,240L1200,320L0,320Z',
      clouds: 'M0,256C200,192,400,288,600,256C800,192,1000,288,1200,256L1200,320L0,320Z',
      book: 'M0,320L1200,320L1200,192C1200,192,1000,240,600,192C200,144,0,192,0,192L0,320Z',
      arrow: 'M0,320L600,192L1200,320L0,320Z'
    };

    const currentPath = computed(() => {
      return shapes[props.data.shapeType as keyof typeof shapes] || shapes.wave;
    });

    const fillColor = computed(() => {
      if (props.data.backgroundType === 'gradient') {
        return 'url(#shapeGradient)';
      }
      return props.data.color;
    });

    const gradientTransform = computed(() => {
      const direction = props.data.gradientDirection;
      if (direction === 'to right') return 'rotate(0)';
      if (direction === 'to left') return 'rotate(180)';
      if (direction === 'to bottom') return 'rotate(90)';
      if (direction === 'to top') return 'rotate(270)';
      if (direction === '45deg') return 'rotate(45)';
      if (direction === '-45deg') return 'rotate(-45)';
      return 'rotate(0)';
    });

    const dividerStyle = computed(() => {
      let transformValue = '';
      
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
        filter: props.data.blur ? `blur(${props.data.blurAmount}px)` : 'none',
        opacity: props.data.shapeImage ? 1 : props.data.opacity / 100,
        height: props.data.height + 'px'
      };
    });

    const backgroundImageStyle = computed(() => ({
      backgroundImage: props.data.backgroundImage ? `url(${props.data.backgroundImage})` : 'none',
      opacity: (props.data.backgroundImageOpacity || 100) / 100,
      height: props.data.height + 'px',
      width: (props.data.width || props.data.density || 100) + '%',
      position: 'absolute' as const,
      top: props.data.position === 'top' ? '0' : 'auto',
      bottom: props.data.position === 'bottom' ? '0' : 'auto',
      left: '0',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      zIndex: -1
    }));

    return {
      currentPath,
      fillColor,
      gradientTransform,
      dividerStyle,
      backgroundImageStyle
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

.banner-container {
  z-index: 1;
}

.background-image {
  z-index: 0;
}

.shape-divider {
  z-index: 1;
}
</style>