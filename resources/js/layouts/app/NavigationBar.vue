<script lang="ts">
import { defineComponent, } from 'vue';
import type { Conference, User } from '@/types/conference';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import { usePage } from '@inertiajs/vue3';

export default defineComponent({
  name: 'NavigationBar',
  components: {
    Badge,
    Button
  },
  data() {
    const conferenceItems = [ // fetch previous conferences from database
      {
        label: '2002',
        url: '/2002'
      },
      {
        label: '2003',
        url: '/2003'
      },
      {
        label: '2004',
        url: '/2004'
      },
      {
        label: '2005',
        url: '/2005'
      },
      {
        label: '2006',
        url: '/2006'
      }
    ];
    return {
      user: null as User | null,
      conferences: [] as Conference[],
      isScrolled: false,
      items: [
        {
          label: 'Home',
          icon: 'pi pi-home',
          url: '/'
        },
        {
          label: 'About',
          icon: 'pi pi-info-circle',
          url: '/about'
        },
        {
          label: 'Schedule',
          icon: 'pi pi-calendar',
          url: '/schedule'
        },
        {
          label: 'Previous Conferences',
          icon: 'pi pi-history',
          items: conferenceItems,
          badge: conferenceItems.length.toString(),
          isOpen: false,
        },
      ],
      isMobileMenuOpen: false
    };
  },
  computed: {
    isAuthenticated(): boolean {
      return !!this.user;
    },
    currentPath(): string {
      return usePage().url;
    }
  },
  methods: {
    async handleLogin(): Promise<void> {
      // Implement login logic here
      console.log('Login clicked');
    },
    async handleLogout(): Promise<void> {
      // Implement logout logic here
      console.log('Logout clicked');
    },
    handleScroll(): void {
      this.isScrolled = window.scrollY > 50;
    },
    toggleMobileMenu(): void {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
    }
  },
  mounted() {
    // Add scroll event listener
    window.addEventListener('scroll', this.handleScroll);
    // Initial check for scroll position
    this.handleScroll();
  },
  beforeUnmount() {
    // Remove scroll event listener
    window.removeEventListener('scroll', this.handleScroll);
  }
});
</script>

<template>
  <nav
    class="landing-container fixed top-4 left-1/2 transform -translate-x-1/2 w-full z-[1000] transition-all duration-300 md:min-w-[360px] max-w-[320px] md:max-w-[720px] lg:max-w-[900px]">
    <div
      class="py-4 pl-4 md:pl-7 pr-4 rounded-3xl lg:rounded-full border border-transparent transition-all duration-300 "
      :class="{ ' py-2 lg:py-2': isScrolled, '': !isScrolled, 'bg-white/80 rounded-3xl lg:rounded-full ': isScrolled }">
      <div class="flex items-center justify-between">
        <!-- Logo section -->
        <div class="flex-1 flex">
          <a href="/" class="w-fit">
            <div class="flex items-center">
              <img class="h-8 w-auto" src="/school-logo.png" alt="School Conference Logo" />
              <span class="ml-3 font-bold text-lg hidden sm:block"
                :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">CMS</span>
            </div>
          </a>
        </div>

        <!-- Desktop navigation links -->
        <ul class="flex-none hidden md:flex items-center gap-2">
          <li v-for="item in items" :key="item.label" class="relative">
            <a v-if="!item.items" :href="item.url"
              class="hover:bg-white/20 rounded-full px-4 py-2 transition-all flex items-center"
              :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
              <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
              <span>{{ item.label }}</span>
              <Badge v-if="(item as any).badge" class="ml-2" :value="(item as any).badge" severity="warning" />
            </a>

            <div v-else class="relative group">
              <a href="#" class="hover:bg-white/20 rounded-full px-4 py-2 transition-all flex items-center"
                :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                <span>{{ item.label }}</span>
                <Badge v-if="(item as any).badge" class="ml-2" :value="(item as any).badge" severity="warning" />
                <i class="pi pi-angle-down ml-2"></i>
              </a>
              <!-- Dropdown menu -->
              <div
                class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden group-hover:block z-10">
                <div class="py-1">
                  <a v-for="subItem in item.items" :key="subItem.label" :href="subItem.url"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    {{ subItem.label }}
                  </a>
                </div>
              </div>
            </div>
          </li>
        </ul>

        <!-- Login/Register buttons -->
        <div class="flex-1 hidden md:flex items-center justify-end gap-4">
          <Button icon="pi pi-key" severity="secondary" variant="text" rounded aria-label="Bookmark"
            @click="handleLogin" class="transition-all duration-200"
            :class="{ 'bg-white': !isScrolled, 'bg-gray-100  hover:bg-indigo-50': isScrolled }">
          </Button>
        </div>

        <!-- Mobile menu button -->
        <button @click="toggleMobileMenu"
          class="flex md:hidden items-center justify-center rounded-lg w-9 h-9 border transition-all"
          :class="{ 'text-white border-white/30 hover:bg-white/20': !isScrolled, 'text-gray-800 border-gray-200 hover:bg-gray-100': isScrolled }">
          <i class="leading-none pi pi-bars"></i>
        </button>
      </div>

      <!-- Mobile menu -->
      <div class="md:hidden block transition-all duration-300 ease-out overflow-hidden"
        :style="{ maxHeight: isMobileMenuOpen ? '500px' : '0px', opacity: isMobileMenuOpen ? '1' : '0' }">
        <div class="flex flex-col gap-8 transition-all pt-4">
          <ul class="flex flex-col gap-2">
            <li v-for="item in items" :key="item.label">
              <a v-if="!item.items" :href="item.url"
                class="flex items-center py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all"
                :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                {{ item.label }}
                <Badge v-if="(item as any).badge" class="ml-2" :value="(item as any).badge" severity="warning" />
              </a>

              <div v-else>
                <button @click="item.isOpen = !item.isOpen"
                  class="flex items-center justify-between py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all"
                  :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                  <div class="flex items-center">
                    <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                    {{ item.label }}
                    <Badge v-if="(item as any).badge" class="ml-2" :value="(item as any).badge" severity="warning" />
                  </div>
                  <i class="pi pi-angle-down"></i>
                </button>
                <!-- Mobile dropdown -->
                <div v-if="item.isOpen" class="pl-4 mt-1">
                  <a v-for="subItem in item.items" :key="subItem.label" :href="subItem.url"
                    class="block py-2 px-4 rounded-lg hover:bg-white/20 transition-all"
                    :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                    {{ subItem.label }}
                  </a>
                </div>
              </div>
            </li>
          </ul>
          <div class="flex flex-col items-center gap-4 pb-4">
            <Button @click="handleLogin" class="rounded-full py-2 w-full transition-all duration-200"
              :class="{ 'bg-white text-indigo-600 hover:bg-indigo-50': !isScrolled, 'bg-gray-100 text-indigo-600 hover:bg-indigo-50': isScrolled }">
              Login
            </Button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.landing-container {
  transform-origin: top center;
}
</style>