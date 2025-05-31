<script lang="ts">
import { defineComponent } from 'vue';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import ConfirmDialog from 'primevue/confirmdialog'; // Add this import
import LoginCard from '../auth/LoginCard.vue';
import Tooltip from 'primevue/tooltip';
import { useAuthStore } from '@/stores/authStore';
import { useConfirm } from 'primevue/useconfirm';

export default defineComponent({
  name: 'Navbar',
  components: {
    Button,
    Avatar,
    ConfirmDialog, // Add this component
    LoginCard
  },
  data() {
    return {
      isScrolled: false,
      loginCardVisible: false,
      regularItems: [
        {
          label: 'Home',
          icon: 'pi pi-home',
          route: '/'
        },
        {
          label: 'Conference Archive',
          icon: 'pi pi-history',
          route: '/archive'
        }
      ],
      adminItems: [
        {
          label: 'Conferences',
          icon: 'pi pi-calendar-plus',
          route: '/dashboard/conferences'
        },
        {
          label: 'Users',
          icon: 'pi pi-user-edit',
          route: '/dashboard/users'
        }
      ],
      isMobileMenuOpen: false
    };
  },
  computed: {
    currentPath(): string {
      return this.$route.path;
    },
    isAuthenticated(): boolean {
      return this.authStore?.isAuthenticated;
    },
    hasAdminAccess(): boolean {
      return this.authStore?.hasAdminAccess;
    },
    currentUser() {
      return this.authStore?.getUser;
    },
    getUserInitials(): string {
      if (!this.currentUser?.name) return 'U';
      return this.currentUser.name
        .split(' ')
        .map((word: string) => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
    },
    getUserRole(): string {
      if (!this.currentUser?.roles?.length) return 'User';
      
      // Prioritize role display: super_admin > admin > editor > other roles
      const roleHierarchy = ['super_admin', 'admin', 'editor'];
      const userRoleNames = this.currentUser.roles.map((role: any) => role.name);
      
      for (const role of roleHierarchy) {
        if (userRoleNames.includes(role)) {
          return role.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase());
        }
      }
      
      // Return first role if none match hierarchy
      return this.currentUser.roles[0].name.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase());
    },
    allItems() {
      // Combine regular items with admin items if user has admin access
      return this.hasAdminAccess ? [...this.regularItems, ...this.adminItems] : this.regularItems;
    }
  },
  methods: {
    showLogin(): void {
      this.loginCardVisible = true;
    },
    confirmLogout(): void {
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
    async handleLogout(): Promise<void> {
      await this.authStore.logout();
      this.$router.push({ name: 'HomePage' });
    },
    async handleLoginSubmit(): Promise<void> {
      console.log('Login submitted');
    },
    handleScroll(): void {
      this.isScrolled = window.scrollY > 50;
    },
    toggleMobileMenu(): void {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
    },
    navigateTo(route: string): void {
      this.$router.push(route);
    }
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
    this.handleScroll();
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  directives: {
    Tooltip
  },
  setup() {
    const authStore = useAuthStore();
    const confirm = useConfirm();
    return { authStore, confirm };
  }
});
</script>

<template>
  <nav class="landing-container fixed left-1/2 -translate-x-1/2 w-full z-[1000] transition-all duration-300 "
    :class="{ 'lg:max-w-[1600px] backdrop-blur-[5px]': !isScrolled, 'md:max-w-[1400px] lg:max-w-[1400px]': isScrolled }">
    <div
      class="bg-surface-50 py-2 pl-4 md:pl-4 pr-4 rounded-3xl md:rounded-full lg:rounded-full border border-transparent transition-all duration-300"
      :class="{ ' shadow backdrop-blur-[5px]': isScrolled }">
      <div class="flex items-center justify-between">

        <div class="flex items-center min-w-0">
          <router-link to="/" class="w-fit flex items-center">
            <img class="h-10 w-auto flex-shrink-0" src="/school-logo.png" alt="School Conference Logo" />
          </router-link>
        </div>

        <ul class="flex-none hidden md:flex items-center gap-4 mx-4">
          <li v-for="item in allItems" :key="item.label" class="relative">
            <router-link :to="item.route"
              class="hover-bg rounded-full px-5 py-2 transition-all duration-200 flex items-center whitespace-nowrap navbar-text font-medium text-sm"
              :class="{ 'active-item shadow': currentPath === item.route }">
              <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
              <span>{{ item.label }}</span>
            </router-link>
          </li>
        </ul>

        <div class="md:flex hidden items-center justify-end gap-4 min-w-0">
          <!-- User Avatar and Info (when authenticated) -->
          <div v-if="isAuthenticated" class="flex items-center gap-2">
            <div class="text-right text-xs hidden lg:block">
              <div class="font-medium">{{ currentUser?.name || 'User' }}</div>
              <div class="text-gray-500">{{ getUserRole }}</div>
            </div>
            <Avatar 
              :label="getUserInitials" 
              size="normal" 
              shape="circle" 
              class="bg-primary-100 text-primary-600 flex-shrink-0"
              v-tooltip.bottom="{ 
                value: `${currentUser?.name} (${getUserRole})`, 
                pt: { text: 'px-3 py-1 text-xs rounded-2xl bg-neutral-900 text-white shadow border-none' } 
              }" 
            />
          </div>

          <Button v-if="isAuthenticated" variant="outlined"
            class="flex items-center rounded-full transition-all duration-200 flex-shrink-0"
            :class="{ 'border-none hover:bg-white/20': !isScrolled, 'bg-white/60 hover:bg-gray-200': isScrolled }"
            v-tooltip.bottom="{ value: 'Go to dashboard', pt: { text: 'px-3 py-1 text-xs rounded-2xl bg-neutral-900 text-white shadow border-none' } }"
            @click="$router.push({ name: 'ConferenceManagement' })">
            <i class="pi pi-th-large text-sm"></i>
          </Button>
          <Button v-if="!isAuthenticated" @click="showLogin" variant="outlined"
            class="flex items-center rounded-full transition-all duration-200 flex-shrink-0"
            :class="{ 'border-none hover:bg-white/20': !isScrolled, 'bg-white/60 hover:bg-gray-200': isScrolled }">
            <i class="pi pi-key text-sm"></i>
          </Button>
          <Button v-else @click="confirmLogout" variant="outlined"
            class="flex items-center rounded-full transition-all duration-200 flex-shrink-0"
            :class="{ 'border-none hover:bg-white/20': !isScrolled, 'bg-white/60 hover:bg-gray-200': isScrolled }"
            v-tooltip.bottom="{ value: 'Logout from your account', pt: { text: 'px-3 py-1 text-xs rounded-2xl bg-neutral-900 text-white shadow border-none' } }">
            <i class="pi pi-sign-out text-sm"></i>
          </Button>
        </div>

        <button @click="toggleMobileMenu"
          class="flex md:hidden items-center justify-center rounded-lg w-9 h-9 border transition-all flex-shrink-0"
          :class="{ 'border-white/30 hover:bg-white/20': !isScrolled, 'border-gray-200 hover:bg-gray-100': isScrolled }">
          <i class="leading-none pi pi-bars"></i>
        </button>
      </div>

      <div class="md:hidden block transition-all duration-300 ease-out overflow-hidden"
        :style="{ maxHeight: isMobileMenuOpen ? '600px' : '0px', opacity: isMobileMenuOpen ? '1' : '0' }">
        <div class="flex flex-col gap-8 transition-all pt-4">
          <ul class="flex flex-col gap-2">
            <li v-for="item in allItems" :key="item.label">
              <router-link :to="item.route"
                class="flex items-center py-2.5 px-4 w-full rounded-xl hover-bg transition-all duration-200 navbar-text shadow"
                :class="{ 'active-item': currentPath === item.route }"
                @click="isMobileMenuOpen = false">
                <i v-if="item.icon" :class="[item.icon, 'mr-3']"></i>
                {{ item.label }}
              </router-link>
            </li>
          </ul>
          <div class="flex flex-col items-center gap-4 pb-4">
            <!-- User info display in mobile -->
            <div v-if="isAuthenticated" class="flex items-center gap-3 w-full p-3 bg-gray-50 rounded-lg">
              <Avatar 
                :label="getUserInitials" 
                size="normal" 
                shape="circle" 
                class="bg-primary-100 text-primary-600"
              />
              <div class="flex-1">
                <div class="font-medium">{{ currentUser?.name || 'User' }}</div>
                <div class="text-sm text-gray-500">{{ getUserRole }}</div>
              </div>
            </div>

            <button v-if="!isAuthenticated" @click="showLogin"
              class="flex items-center py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all text-left">
              <i class="pi pi-key mr-2"></i>
              Login
            </button>
            <button v-if="isAuthenticated" @click="$router.push({ name: 'ConferenceManagement' })"
              class="flex items-center py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all text-left">
              <i class="pi pi-th-large mr-2"></i>
              Go to dashboard
            </button>
            <button v-if="isAuthenticated" @click="confirmLogout"
              class="flex items-center py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all text-left">
              <i class="pi pi-sign-out mr-2"></i>
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Add ConfirmDialog component -->
    <ConfirmDialog />
    <LoginCard v-model:visible="loginCardVisible" @login="handleLoginSubmit" />
  </nav>
</template>