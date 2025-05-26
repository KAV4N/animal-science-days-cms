<template>
  <div class="dashboard-container bg-surface-200">
    <Sidebar 
      :visible="sidebarVisible" 
      v-model:mobileVisible="mobileDrawerVisible"
    />
    
    <Topbar 
      :sidebarVisible="sidebarVisible" 
      @toggle-mobile-menu="toggleMobileDrawer"
    />
    
    <div class="main-content" :class="{ 'with-sidebar': sidebarVisible }">
      <div class="content-area">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useRouter } from 'vue-router';
import Sidebar from '@/components/dashboard/Sidebar.vue';
import Topbar from '@/components/dashboard/Topbar.vue';

export default defineComponent({
  name: 'Dashboard',
  components: {
    Sidebar,
    Topbar
  },
  data() {
    return {
      sidebarVisible: true,
      mobileDrawerVisible: false
    };
  },
  setup() {
    const router = useRouter();
    return { router };
  },
  mounted() {
    this.checkScreenSize();
    window.addEventListener('resize', this.checkScreenSize);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkScreenSize);
  },
  methods: {
    toggleMobileDrawer() {
      this.mobileDrawerVisible = !this.mobileDrawerVisible;
    },
    checkScreenSize() {
      if (window.innerWidth < 768) {
        this.sidebarVisible = false;
      } else {
        this.sidebarVisible = true;
        // Close mobile drawer if screen is resized to desktop
        this.mobileDrawerVisible = false;
      }
    }
  }
});
</script>

<style scoped>
.dashboard-container {
  display: flex;
  height: 100vh;
  width: 100%;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
  transition: margin-left 0.3s ease;
  margin-top: 48px; 
}

.main-content.with-sidebar {
  margin-left: 220px; 
}

.content-area {
  padding: 1rem;
  width: 100%;
  height: calc(100vh - 48px); 
  overflow-y: auto;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}
</style>