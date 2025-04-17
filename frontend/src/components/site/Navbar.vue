<script lang="ts">
import { defineComponent } from 'vue';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import LoginCard from '../auth/LoginCard.vue';

interface Conference {
  label: string;
  url: string;
}

interface User {
  id: string;
  name: string;
}

export default defineComponent({
  name: 'Navbar',
  components: {
    Badge,
    Button,
    LoginCard
  },
  data() {
    const conferenceItems = [
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
      loginCardVisible: false,
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
          label: 'Conferences',
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
      return window.location.pathname;
    }
  },
  methods: {
    showLogin(): void {
      this.loginCardVisible = true;
    },
    async handleLoginSubmit(): Promise<void> {
      console.log('Login submitted');
    },
    handleScroll(): void {
      this.isScrolled = window.scrollY > 50;
    },
    toggleMobileMenu(): void {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
    }
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
    this.handleScroll();
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  }
});
</script>

<template>
  <nav class="landing-container fixed top-4 left-1/2 -translate-x-1/2 w-full z-[1000] transition-all duration-300 "
    :class="{ 'lg:max-w-[1190px] backdrop-blur-[5px]': !isScrolled, 'md:max-w-[720px] lg:max-w-[900px] ': isScrolled }">
    <div
      class="py-2 pl-4 md:pl-4 pr-4 rounded-3xl md:rounded-full lg:rounded-full border border-transparent transition-all duration-300"
      :class="{ 'bg-surface-200/60 shadow-lg backdrop-blur-[5px]': isScrolled }">
      <div class="flex items-center justify-between">

        <!-- Existing Navbar Content (unchanged) -->
        <div class="flex items-center min-w-0">
          <a href="/" class="w-fit flex items-center">
            <img class="h-10 w-auto flex-shrink-0" src="/school-logo.png" alt="School Conference Logo" />
          </a>
        </div>

        <ul class="flex-none hidden md:flex items-center gap-2 mx-4">
          <li v-for="item in items" :key="item.label" class="relative">
            <a v-if="!item.items" :href="item.url"
              class="hover:bg-white/20 rounded-full px-4 py-2 transition-all flex items-center whitespace-nowrap"
              :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
              <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
              <span>{{ item.label }}</span>
              <Badge v-if="(item as any).badge" class="ml-2 flex-shrink-0" :value="(item as any).badge"
                severity="warning" />
            </a>

            <div v-else class="relative">
              <button @click="item.isOpen = !item.isOpen"
                class="hover:bg-white/20 rounded-full px-4 py-2 transition-all flex items-center whitespace-nowrap"
                :class="[
                  { 'text-white': !isScrolled, 'text-gray-800': isScrolled },
                  { 'bg-white/20': item.isOpen }
                ]">
                <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                <span>{{ item.label }}</span>
                <Badge v-if="(item as any).badge" class="ml-2 flex-shrink-0" :value="(item as any).badge"
                  severity="warning" />
                <i class="pi pi-angle-down ml-2"></i>
              </button>

              <div v-if="item.isOpen" class="absolute left-2 mt-2 w-48 rounded-md shadow-lg z-10  backdrop-blur-[5px]"
                :class="{ 'bg-white/20 text-white': !isScrolled, 'bg-surface-200/60 text-gray-800': isScrolled }">
                <div class="py-1">
                  <a v-for="subItem in item.items" :key="subItem.label" :href="subItem.url"
                    class="block px-4 py-2 text-sm hover:bg-white/20 transition-all"
                    :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                    {{ subItem.label }}
                  </a>
                </div>
              </div>
            </div>
          </li>
        </ul>

        <div class="md:flex hidden items-center justify-end gap-4 min-w-0">
          <Button @click="showLogin" variant="outlined"
            class="flex items-center rounded-full transition-all duration-200 flex-shrink-0"
            :class="{ 'text-white border-none hover:bg-white/20': !isScrolled, 'bg-white/60 text-gray-800 hover:bg-gray-200': isScrolled }">
            <i class="pi pi-key text-sm"></i>
          </Button>
        </div>

        <button @click="toggleMobileMenu"
          class="flex md:hidden items-center justify-center rounded-lg w-9 h-9 border transition-all flex-shrink-0"
          :class="{ 'text-white border-white/30 hover:bg-white/20': !isScrolled, 'text-gray-800 border-gray-200 hover:bg-gray-100': isScrolled }">
          <i class="leading-none pi pi-bars"></i>
        </button>
      </div>

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
                  class="flex items-center justify-between py-2 px-4 w-full  rounded-full hover:bg-white/20 transition-all"
                  :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                  <div class="flex items-center">
                    <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                    {{ item.label }}
                    <Badge v-if="(item as any).badge" class="ml-2" :value="(item as any).badge" severity="warning" />
                  </div>
                  <i class="pi pi-angle-down"></i>
                </button>

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
            <button @click="showLogin"
              class="flex items-center py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all text-left"
              :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
              <i class="pi pi-key mr-2"></i>
              Login
            </button>
          </div>
        </div>
      </div>
    </div>
    <LoginCard
      v-model:visible="loginCardVisible"
      @login="handleLoginSubmit"
    />
  </nav>
</template>

<style scoped>
.landing-container {
  transform-origin: top center;
}
</style>
