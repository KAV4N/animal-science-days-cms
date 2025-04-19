<template>
  <!-- Desktop Sidebar using Card -->
  <Card v-if="!isMobileView" class="static-sidebar shadow" :class="{ 'hidden': !visible }" :pt="cardPt" style="border-radius: 0px;">
    <template #content>
      <div class="flex flex-col h-full">
        <!-- Logo/Header -->
        <div class="flex items-center justify-between px-4 py-3 shrink-0">
          <span class="inline-flex items-center">
            <span class="font-semibold text-lg">BOKU</span>
          </span>
        </div>
  
        <!-- Scrollable Content - Make it flex-grow to push user profile to bottom -->
        <div class="overflow-y-auto flex-grow">
          <!-- Management Section -->
          <ul class="list-none p-2 m-0">
            <li>
              <div
                v-ripple
                v-styleclass="{
                  selector: '@next',
                  enterFromClass: 'hidden',
                  enterActiveClass: 'animate-slidedown',
                  leaveToClass: 'hidden',
                  leaveActiveClass: 'animate-slideup'
                }"
                class="p-2 flex items-center justify-between cursor-pointer p-ripple text-sm"
                @click="toggleSection('management')"
              >
                <span class="font-medium">MANAGEMENT</span>
                <i class="pi" :class="{'pi-chevron-down': sectionsExpanded.management, 'pi-chevron-right': !sectionsExpanded.management}"></i>
              </div>
              <ul class="list-none p-0 m-0 overflow-hidden" :class="{ 'hidden': !sectionsExpanded.management }">
                <li>
                  <router-link 
                    v-ripple 
                    to="/dashboard/conferences" 
                    class="flex items-center cursor-pointer p-2 rounded duration-150 transition-colors p-ripple text-sm"
                    :class="{ 'bg-active': isActiveRoute('/dashboard/conferences') }"
                  >
                    <i class="pi pi-calendar-plus mr-2"></i>
                    <span class="font-medium">Conference Management</span>
                  </router-link>
                </li>
                <li>
                  <router-link 
                    v-ripple 
                    to="/dashboard/users" 
                    class="flex items-center cursor-pointer p-2 rounded duration-150 transition-colors p-ripple text-sm"
                    :class="{ 'bg-active': isActiveRoute('/dashboard/users') }"
                  >
                    <i class="pi pi-user-edit mr-2"></i>
                    <span class="font-medium">User Management</span>
                  </router-link>
                </li>
              </ul>
            </li>
          </ul>
        </div>
        
        <!-- User Profile - Explicitly at the bottom -->
        <div class="mt-auto shrink-0">
          <hr class="mb-2 mx-2 border-t border-0" />
          <a v-ripple class="flex items-center cursor-pointer p-2 gap-2 rounded duration-150 transition-colors p-ripple text-sm" @click="handleLogout">
            <Avatar icon="pi pi-sign-out" size="small" shape="circle" />
            <span class="font-medium">Logout</span>
          </a>
        </div>
      </div>
    </template>
  </Card>

  <!-- Mobile Sidebar (Drawer) - No Card here -->
  <Drawer v-model:visible="mobileDrawerVisible" :pt="{ root: { class: 'mobile-sidebar-drawer' } }" class="md:hidden">
    <template #container="{ closeCallback }">
      <div class="flex flex-col h-full">
        <!-- Logo/Header -->
        <div class="flex items-center justify-between px-4 pt-2 shrink-0">
          <span class="inline-flex items-center">
            <span class="font-semibold text-lg">BOKU</span>
          </span>
          <span>
            <Button type="button" @click="closeCallback" icon="pi pi-times" rounded outlined size="small"></Button>
          </span>
        </div>
        
        <!-- Scrollable Content -->
        <div class="overflow-y-auto flex-grow">
          <!-- Management Section -->
          <ul class="list-none p-2 m-0">
            <li>
              <div
                v-ripple
                v-styleclass="{
                  selector: '@next',
                  enterFromClass: 'hidden',
                  enterActiveClass: 'animate-slidedown',
                  leaveToClass: 'hidden',
                  leaveActiveClass: 'animate-slideup'
                }"
                class="p-2 flex items-center justify-between cursor-pointer p-ripple text-sm"
                @click="toggleMobileSection('management')"
              >
                <span class="font-medium">MANAGEMENT</span>
                <i class="pi" :class="{'pi-chevron-down': mobileSectionsExpanded.management, 'pi-chevron-right': !mobileSectionsExpanded.management}"></i>
              </div>
              <ul class="list-none p-0 m-0 overflow-hidden">
                <li>
                  <router-link 
                    v-ripple 
                    to="/dashboard/conferences" 
                    class="flex items-center cursor-pointer p-2 rounded duration-150 transition-colors p-ripple text-sm"
                    :class="{ 'bg-active': isActiveRoute('/dashboard/conferences') }"
                    @click="closeMobileDrawer"
                  >
                    <i class="pi pi-calendar-plus mr-2"></i>
                    <span class="font-medium">Conference Management</span>
                  </router-link>
                </li>
                <li>
                  <router-link 
                    v-ripple 
                    to="/dashboard/users" 
                    class="flex items-center cursor-pointer p-2 rounded duration-150 transition-colors p-ripple text-sm"
                    :class="{ 'bg-active': isActiveRoute('/dashboard/users') }"
                    @click="closeMobileDrawer"
                  >
                    <i class="pi pi-user-edit mr-2"></i>
                    <span class="font-medium">User Management</span>
                  </router-link>
                </li>
              </ul>
            </li>
          </ul>
        </div>
        
        <!-- User Profile -->
        <div class="mt-auto shrink-0">
          <hr class="mb-2 mx-2 border-t border-0" />
          <a v-ripple class="flex items-center cursor-pointer p-2 gap-2 rounded duration-150 transition-colors p-ripple text-sm" @click="handleLogout">
            <Avatar icon="pi pi-sign-out" size="small" shape="circle" />
            <span class="font-medium">Logout</span>
          </a>
        </div>
      </div>
    </template>
  </Drawer>
</template>

<script>
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Drawer from 'primevue/drawer';
import Card from 'primevue/card';

export default {
  name: 'DashboardSidebar',
  components: {
    Avatar,
    Button,
    Drawer,
    Card
  },
  props: {
    visible: {
      type: Boolean,
      default: true
    },
    mobileVisible: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      mobileDrawerVisible: false,
      sectionsExpanded: {
        management: true
      },
      mobileSectionsExpanded: {
        management: true
      },
      isMobileView: false,
      cardPt: {
        // Pass through props to remove padding and set proper styling
        body: { class: 'p-0' },
        content: { class: 'p-0' },
        root: { class: 'border-none' }
      }
    };
  },
  created() {
    this.checkViewport();
    window.addEventListener('resize', this.checkViewport);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkViewport);
  },
  watch: {
    mobileVisible: {
      immediate: true,
      handler(newVal) {
        this.mobileDrawerVisible = newVal;
      }
    },
    mobileDrawerVisible(newVal) {
      this.$emit('update:mobileVisible', newVal);
    }
  },
  methods: {
    checkViewport() {
      this.isMobileView = window.innerWidth < 768;
    },
    toggleSection(section) {
      this.sectionsExpanded[section] = !this.sectionsExpanded[section];
    },
    toggleMobileSection(section) {
      this.mobileSectionsExpanded[section] = !this.mobileSectionsExpanded[section];
    },
    isActiveRoute(path) {
      return this.$route.path === path || this.$route.path.startsWith(`${path}/`);
    },
    closeMobileDrawer() {
      this.mobileDrawerVisible = false;
    },
    handleLogout() {
      this.$emit('logout');
    }
  }
};
</script>

<style scoped>
.static-sidebar {
  width: 220px; /* Reduced from 280px */
  height: 100vh;
  flex-shrink: 0;
  z-index: 10;
  position: fixed;
  top: 0;
  left: 0;
}

:deep(.p-card) {
  height: 100%;
  border-radius: 0;
}

:deep(.p-card-body) {
  padding: 0;
  height: 100%;
}

:deep(.p-card-content) {
  padding: 0;
  height: 100%;
  display: flex;
  flex-direction: column;
}

:deep(.mobile-sidebar-drawer) {
  width: 220px !important; /* Reduced from 280px */
  height: 100vh;
}

:deep(.p-drawer-content) {
  background-color: var(--surface-card);
  padding: 0;
  display: flex;
  flex-direction: column;
  height: 100%;
}

/* Responsive styles */
@media (max-width: 768px) {
  .static-sidebar {
    display: none;
  }
  
  .static-sidebar.hidden {
    display: none;
  }
}
</style>