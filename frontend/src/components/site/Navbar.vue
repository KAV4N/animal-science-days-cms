<template>
  <nav
    class="landing-container fixed left-1/2 -translate-x-1/2 w-full z-[1000] transition-all duration-300 backdrop-blur-[5px] navbar-container"
    :class="{ 'lg:max-w-[1350px]': !isScrolled, 'lg:max-w-[1050px] py-1 lg:rounded-full navbar-container shadow': isScrolled }"
  >
    <div
      class="py-2 pl-4 md:pl-4 pr-4 rounded-3xl md:rounded-full lg:rounded-full  backdrop-blur-[5px] transition-all duration-300"
    >
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center min-w-0">
          <a href="/" class="w-fit flex items-center">
            <img class="h-10 w-auto flex-shrink-0" src="/school-logo.png" alt="School Conference Logo" />
          </a>
        </div>

        <!-- Desktop Menu -->
        <ul class="flex-none hidden md:flex items-center gap-3 mx-4">
              <li v-for="item in items" :key="item.label" class="relative">
                <a v-if="!item.items" :href="item.url"
                  class="hover-bg rounded-full px-5 py-2 transition-all duration-200 flex items-center whitespace-nowrap navbar-text font-medium text-sm"
                  :class="{ 'active-item shadow': currentPath === item.url }">
                  <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                  <span>{{ item.label }}</span>
                  <Badge v-if="(item as any).badge" class="ml-2 flex-shrink-0" :value="(item as any).badge"
                    severity="info" />
                </a>

                <div v-else class="relative">
                  <button @click="item.isOpen = !item.isOpen"
                    class="hover-bg rounded-full px-5 py-2 transition-all duration-200 flex items-center whitespace-nowrap navbar-text font-medium text-sm"
                    :class="{ 'active-item shadow': item.isOpen }">
                    <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                    <span>{{ item.label }}</span>
                    <Badge v-if="(item as any).badge" class="ml-2 flex-shrink-0" :value="(item as any).badge"
                      severity="info" />
                    <i class="pi pi-chevron-down ml-2 text-xs"></i>
                  </button>

                  <div v-if="item.isOpen" 
                    class="absolute left-0 mt-2 w-52 rounded-xl shadow z-10 dropdown-menu  overflow-hidden">
                    <div class="py-2">
                      <a v-for="subItem in item.items" :key="subItem.label" :href="subItem.url"
                        class="block px-5 py-2.5 text-sm dropdown-item transition-all duration-200 navbar-text flex items-center">
                        <span class="w-2 h-2 rounded-full dropdown-bullet mr-2.5"></span>
                        {{ subItem.label }}
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
   

        <!-- Desktop Login Button -->
        <div class="md:flex hidden items-center justify-end gap-4 min-w-0">
              <Button @click="showLogin" 
              class="hover-bg rounded-full py-2 transition-all duration-200 flex items-center whitespace-nowrap navbar-text navbar-button font-medium text-sm shadow">
                <i class="pi pi-user text-sm mr-2"></i>
                <span>Login</span>
              </Button>
            </div>

        <!-- Mobile Toggle Button -->
        <button
          @click="toggleMobileMenu"
          class="flex md:hidden items-center justify-center rounded-lg w-9 h-9  navbar-button transition-all shadow flex-shrink-0"
        >
          <i class="leading-none pi pi-bars"></i>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div
        class="md:hidden block transition-all duration-300 ease-out overflow-hidden"
        :style="{ maxHeight: isMobileMenuOpen ? '500px' : '0px', opacity: isMobileMenuOpen ? '1' : '0' }"
      >
        <div class="flex flex-col gap-8 transition-all pt-4">
          <ul class="flex flex-col gap-2">
                <li v-for="item in items" :key="item.label">
                  <a v-if="!item.items" :href="item.url"
                    class="flex items-center py-2.5 px-4 w-full rounded-xl hover-bg transition-all duration-200 navbar-text shadow"
                    :class="{ 'active-item': currentPath === item.url }">
                    <i v-if="item.icon" :class="[item.icon, 'mr-3']"></i>
                    {{ item.label }}
                    <Badge v-if="(item as any).badge" class="ml-2" :value="(item as any).badge" severity="info" />
                  </a>

                  <div v-else>
                    <button @click="item.isOpen = !item.isOpen"
                      class="flex items-center justify-between py-2.5 px-4 w-full rounded-xl hover-bg transition-all duration-200 navbar-text shadow"
                      :class="{ 'active-item': item.isOpen }">
                      <div class="flex items-center">
                        <i v-if="item.icon" :class="[item.icon, 'mr-3']"></i>
                        {{ item.label }}
                        <Badge v-if="(item as any).badge" class="ml-2" :value="(item as any).badge" severity="info" />
                      </div>
                      <i class="pi" :class="item.isOpen ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
                    </button>

                    <div v-if="item.isOpen" class="pl-4 mt-1 border-l-2 mobile-submenu-border ml-4 mobile-submenu">
                      <a v-for="subItem in item.items" :key="subItem.label" :href="subItem.url"
                        class="block py-2.5 px-4 rounded-xl hover-bg transition-all duration-200 navbar-text text-sm flex items-center">
                        <span class="w-2 h-2 rounded-full dropdown-bullet mr-2.5"></span>
                        {{ subItem.label }}
                      </a>
                    </div>
                  </div>
                </li>
              </ul>

          <!-- Mobile Login Button -->
          <div class="flex flex-col items-center gap-4 pb-2">
            <button @click="showLogin"
              class="flex items-center justify-center py-2.5 px-6 w-full rounded-xl transition-all duration-200 text-center navbar-button shadow font-medium">
              <i class="pi pi-user mr-2"></i>
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



<style scoped>
/* PrimeVue theme integration */
.navbar-container {
  background-color: var(--primary-color);
  border-color: rgba(255, 255, 255, 0.1);
}

.navbar-text {
  color: var(--primary-color-text);
}

.hover-bg:hover {
  background-color: rgba(255, 255, 255, 0.15);
}

.active-item {
  background-color: rgba(255, 255, 255, 0.2);
}

.navbar-button {
  background-color: var(--primary-600);
  color: var(--primary-color-text);
  border: none;
}

.navbar-button:hover {
  background-color: var(--primary-700);
}

.dropdown-menu {
  background-color: var(--primary-600);
  border-color: rgba(255, 255, 255, 0.1);
}

.dropdown-blur {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.dropdown-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.dropdown-bullet {
  background-color: var(--primary-200);
}

.mobile-toggle {
  background-color: rgba(255, 255, 255, 0.1);
  color: var(--primary-color-text);
}

.mobile-toggle:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.mobile-submenu-border {
  border-color: var(--primary-200);
}

.mobile-submenu {
  background-color: rgba(255, 255, 255, 0.05);
  border-radius: 0.5rem;
  margin-top: 0.5rem;
}

</style>