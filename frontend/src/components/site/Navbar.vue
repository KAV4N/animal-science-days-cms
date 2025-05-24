<script lang="ts">
import { defineComponent } from 'vue';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import LoginCard from '../auth/LoginCard.vue';


export default defineComponent({
  name: 'Navbar',
  components: {
    Badge,
    Button,
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
      isMobileMenuOpen: false
    };
  },
  computed: {
    currentPath(): string {
      return this.$route.path;
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
  }
});
</script>

<template>
  <nav class="landing-container fixed left-1/2 -translate-x-1/2 w-full z-[1000] transition-all duration-300"
    :class="{ 'lg:max-w-[1350px] backdrop-blur-[5px]': !isScrolled, 'md:max-w-[1050px] lg:max-w-[1050px]': isScrolled }">
    <div
      class="py-2 pl-4 md:pl-4 pr-4 rounded-3xl md:rounded-full lg:rounded-full border border-transparent transition-all duration-300"
      :class="{ ' shadow backdrop-blur-[5px]': isScrolled }">
      <div class="flex items-center justify-between">

        <div class="flex items-center min-w-0">
          <router-link to="/" class="w-fit flex items-center">
            <img class="h-10 w-auto flex-shrink-0" src="/school-logo.png" alt="School Conference Logo" />
          </router-link>
        </div>

        <ul class="flex-none hidden md:flex items-center gap-4 mx-4">
          <li v-for="item in regularItems" :key="item.label" class="relative">
            <router-link
              :to="item.route"
              class="hover-bg rounded-full px-5 py-2 transition-all duration-200 flex items-center whitespace-nowrap navbar-text font-medium text-sm"
              :class="{ 'active-item shadow': currentPath === item.route }"
            >
              <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
              <span>{{ item.label }}</span>
              <Badge v-if="item.badge" class="ml-2 flex-shrink-0" :value="item.badge" severity="info" />
            </router-link>
          </li>
        </ul>

        <div class="md:flex hidden items-center justify-end gap-4 min-w-0">
          <Button @click="showLogin" variant="outlined"
            class="flex items-center rounded-full transition-all duration-200 flex-shrink-0"
            :class="{ 'border-none hover:bg-white/20': !isScrolled, 'bg-white/60 hover:bg-gray-200': isScrolled }">
            <i class="pi pi-key text-sm"></i>
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
            <li v-for="item in regularItems" :key="item.label">
              <router-link
                :to="item.route"
                class="flex items-center py-2.5 px-4 w-full rounded-xl hover-bg transition-all duration-200 navbar-text shadow"
                :class="{ 'active-item': currentPath === item.route }"
              >
                <i v-if="item.icon" :class="[item.icon, 'mr-3']"></i>
                {{ item.label }}
                <Badge v-if="item.badge" class="ml-2" :value="item.badge" severity="info" />
              </router-link>
            </li>
          </ul>
          <div class="flex flex-col items-center gap-4 pb-4">
            <button @click="showLogin"
              class="flex items-center py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all text-left">
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
