<template>
  <!-- Desktop Sidebar using Card -->
  <Card v-if="!isMobileView" class="static-sidebar shadow-lg border-e" :class="{ 'hidden': !visible }" :pt="cardPt" style="border-radius: 0px;">
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
          <!-- Navigation Items -->
          <ul class="list-none p-2 m-0">
            <li>
              <router-link 
                v-ripple 
                to="/dashboard/conferences" 
                class="flex items-center cursor-pointer p-2 rounded p-ripple text-sm"
                :class="{ 'bg-primary-50 text-primary-600': isActiveRoute('/dashboard/conferences') }"
              >
                <i class="pi pi-calendar-plus mr-2"></i>
                <span class="font-medium">Conference Management</span>
              </router-link>
            </li>
            <li v-if="hasAdminAccess">
              <router-link 
                v-ripple 
                to="/dashboard/users" 
                class="flex items-center cursor-pointer p-2 rounded p-ripple text-sm"
                :class="{ 'bg-primary-50 text-primary-600': isActiveRoute('/dashboard/users') }"
              >
                <i class="pi pi-user-edit mr-2"></i>
                <span class="font-medium">User Management</span>
              </router-link>
            </li>
          </ul>
        </div>
        
        <!-- User Profile - Explicitly at the bottom -->
        <div class="mt-auto shrink-0">
          <hr class="mb-2 mx-2 border-t border-0" />
          <!-- User Info Display -->
          <div class="px-2 py-1 mb-2">
            <div class="flex items-center gap-2 text-xs text-gray-600">
              <Avatar :label="getUserInitials" size="small" shape="circle" class="bg-primary-100 text-primary-600" />
              <div class="flex-1 min-w-0">
                <div class="font-medium truncate">{{ currentUser?.name || 'User' }}</div>
                <div class="text-xs text-gray-500 truncate">{{ getUserRole }}</div>
              </div>
            </div>
          </div>
          <a v-ripple class="flex items-center cursor-pointer p-2 gap-2 rounded p-ripple text-sm hover:bg-gray-50" @click="confirmLogout">
            <Avatar icon="pi pi-sign-out" size="small" shape="circle" class="bg-red-100 text-red-600" />
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
          <!-- Navigation Items -->
          <ul class="list-none p-2 m-0">
            <li v-if="hasAdminAccess">
              <router-link 
                v-ripple 
                to="/dashboard/conferences" 
                class="flex items-center cursor-pointer p-2 rounded p-ripple text-sm"
                :class="{ 'bg-primary-50 text-primary-600': isActiveRoute('/dashboard/conferences') }"
                @click="closeMobileDrawer"
              >
                <i class="pi pi-calendar-plus mr-2"></i>
                <span class="font-medium">Conference Management</span>
              </router-link>
            </li>
            <li v-if="hasAdminAccess">
              <router-link 
                v-ripple 
                to="/dashboard/users" 
                class="flex items-center cursor-pointer p-2 rounded p-ripple text-sm"
                :class="{ 'bg-primary-50 text-primary-600': isActiveRoute('/dashboard/users') }"
                @click="closeMobileDrawer"
              >
                <i class="pi pi-user-edit mr-2"></i>
                <span class="font-medium">User Management</span>
              </router-link>
            </li>
            <!-- Show message if no admin access -->
            <li v-if="!hasAdminAccess" class="p-2 text-sm text-gray-500">
              <i class="pi pi-lock mr-2"></i>
              <span>Admin access required</span>
            </li>
          </ul>
        </div>
        
        <!-- User Profile -->
        <div class="mt-auto shrink-0">
          <hr class="mb-2 mx-2 border-t border-0"/>
          <!-- User Info Display -->
          <div class="px-2 py-1 mb-2">
            <div class="flex items-center gap-2 text-xs text-gray-600">
              <Avatar :label="getUserInitials" size="small" shape="circle" class="bg-primary-100 text-primary-600" />
              <div class="flex-1 min-w-0">
                <div class="font-medium truncate">{{ currentUser?.name || 'User' }}</div>
                <div class="text-xs text-gray-500 truncate">{{ getUserRole }}</div>
              </div>
            </div>
          </div>
          <a v-ripple class="flex items-center cursor-pointer p-2 gap-2 rounded p-ripple text-sm hover:bg-gray-50" @click="confirmLogout">
            <Avatar icon="pi pi-sign-out" size="small" shape="circle" class="bg-red-100 text-red-600" />
            <span class="font-medium">Logout</span>
          </a>
        </div>
      </div>
    </template>
  </Drawer>
  
  <ConfirmDialog />
</template>

<script>
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Drawer from 'primevue/drawer';
import Card from 'primevue/card';
import { useAuthStore } from '@/stores/authStore';
import { useConfirm } from 'primevue/useconfirm';

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
      isMobileView: false,
      cardPt: {
        // Pass through props to remove padding and set proper styling
        body: { class: 'p-0' },
        content: { class: 'p-0' },
        root: { class: 'border-none' }
      }
    };
  },
  computed: {
    hasAdminAccess() {
      return this.authStore.hasAdminAccess;
    },
    currentUser() {
      return this.authStore.getUser;
    },
    getUserInitials() {
      if (!this.currentUser?.name) return 'U';
      return this.currentUser.name
        .split(' ')
        .map(word => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
    },
    getUserRole() {
      if (!this.currentUser?.roles?.length) return 'User';
      
      // Prioritize role display: super_admin > admin > editor > other roles
      const roleHierarchy = ['super_admin', 'admin', 'editor'];
      const userRoleNames = this.currentUser.roles.map(role => role.name);
      
      for (const role of roleHierarchy) {
        if (userRoleNames.includes(role)) {
          return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        }
      }
      
      // Return first role if none match hierarchy
      return this.currentUser.roles[0].name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
  },
  setup() {
    const authStore = useAuthStore();
    const confirm = useConfirm();
    return { authStore, confirm };
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
    isActiveRoute(path) {
      return this.$route.path === path || this.$route.path.startsWith(`${path}/`);
    },
    closeMobileDrawer() {
      this.mobileDrawerVisible = false;
    },
    confirmLogout() {
      this.confirm.require({
        message: 'Naozaj sa chcete odhlásiť?',
        header: 'Potvrdenie odhlásenia',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
          label: 'Zrušiť',
          severity: 'secondary',
          outlined: true
        },
        acceptProps: {
          label: 'Odhlásiť',
          severity: 'danger'
        },
        accept: () => {
          this.handleLogout();
        }
      });
    },
    async handleLogout() {
      await this.authStore.logout();
      this.$router.push({ name: 'HomePage' });
    }
  }
};
</script>

<style scoped>
.static-sidebar {
  width: 220px; 
  border-right-width: 1px !important;
  height: 100vh;
  flex-shrink: 0;
  z-index: 10;
  position: fixed;
  top: 0;
  left: 0;
}

:deep(.p-card) {
  height: 100%;
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
  width: 220px !important; 
  height: 100vh;
}

:deep(.p-drawer-content) {
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