<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import { usePastConferencesStore } from '@/stores/pastConferencesStore';
import LoginCard from '../auth/LoginCard.vue';
import { useRouter } from 'vue-router';

// States
const isScrolled = ref(false);
const loginCardVisible = ref(false);
const isMobileMenuOpen = ref(false);
const navItems = ref([
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
]);

// Conference dropdown
const conferenceDropdownOpen = ref(false);
const pastConferencesStore = usePastConferencesStore();
const router = useRouter();

const recentConferences = computed(() => {
  return pastConferencesStore.recentConferences(3);
});

onMounted(async () => {
  // Handle scroll event
  window.addEventListener('scroll', handleScroll);
  handleScroll();
  
  // Fetch past conferences
  await pastConferencesStore.fetchConferences();
});

// Methods
function handleScroll() {
  isScrolled.value = window.scrollY > 50;
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function toggleConferenceDropdown() {
  conferenceDropdownOpen.value = !conferenceDropdownOpen.value;
}

function showAllConferences() {
  conferenceDropdownOpen.value = false;
  isMobileMenuOpen.value = false;
  router.push('/conferences');
}

function showLogin() {
  loginCardVisible.value = true;
}

function handleLoginSubmit() {
  console.log('Login submitted');
}
</script>

<template>
  <nav class="landing-container fixed top-4 left-1/2 -translate-x-1/2 w-full z-[1000] transition-all duration-300 "
    :class="{ 'lg:max-w-[1190px] backdrop-blur-[5px]': !isScrolled, 'md:max-w-[720px] lg:max-w-[900px] ': isScrolled }">
    <div
      class="py-2 pl-4 md:pl-4 pr-4 rounded-3xl md:rounded-full lg:rounded-full border border-transparent transition-all duration-300"
      :class="{ 'bg-surface-200/60 shadow-lg backdrop-blur-[5px]': isScrolled }">
      <div class="flex items-center justify-between">

        <div class="flex items-center min-w-0">
          <a href="/" class="w-fit flex items-center">
            <img class="h-10 w-auto flex-shrink-0" src="/school-logo.png" alt="School Conference Logo" />
          </a>
        </div>

        <ul class="flex-none hidden md:flex items-center gap-2 mx-4">
          <li v-for="item in navItems" :key="item.label" class="relative">
            <a :href="item.url"
              class="hover:bg-white/20 rounded-full px-4 py-2 transition-all flex items-center whitespace-nowrap"
              :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
              <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
              <span>{{ item.label }}</span>
            </a>
          </li>

          <li class="relative">
            <button @click="toggleConferenceDropdown"
              class="hover:bg-white/20 rounded-full px-4 py-2 transition-all flex items-center whitespace-nowrap"
              :class="[
                { 'text-white': !isScrolled, 'text-gray-800': isScrolled },
                { 'bg-white/20': conferenceDropdownOpen }
              ]">
              <i class="pi pi-bookmark mr-2"></i>
              <span>Past Conferences</span>
              <Badge v-if="recentConferences.length" class="ml-2 flex-shrink-0" :value="recentConferences.length"
                severity="info" />
              <i class="pi pi-angle-down ml-2"></i>
            </button>

            <div v-if="conferenceDropdownOpen"
              class="absolute left-2 mt-2 w-64 rounded-md shadow-lg z-10 backdrop-blur-[5px]"
              :class="{ 'bg-white/20 text-white': !isScrolled, 'bg-surface-200/60 text-gray-800': isScrolled }">
              <div class="py-1">
                <!-- Recent conferences -->
                <div class="px-4 py-2 text-sm font-medium opacity-70">Recent Conferences</div>
                <template v-if="recentConferences.length">
                  <a v-for="conf in recentConferences" :key="conf.id" 
                     :href="`/conference/${conf.slug}`"
                     class="block px-4 py-2 text-sm hover:bg-white/20 transition-all flex items-center"
                     :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                    <span class="mr-2">{{ conf.title }}</span>
                    <span class="text-xs opacity-70">{{ new Date(conf.startDate).getFullYear() }}</span>
                  </a>
                </template>
                <div v-else class="px-4 py-2 text-sm italic opacity-60">
                  No recent conferences
                </div>
                
                <div class="border-t border-white/10 mt-1">
                  <button @click="showAllConferences"
                    class="w-full text-left px-4 py-2 text-sm font-medium hover:bg-white/20 transition-all flex items-center"
                    :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                    <i class="pi pi-arrow-right mr-2"></i>
                    <span>Browse all past conferences</span>
                  </button>
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
            <li v-for="item in navItems" :key="item.label">
              <a :href="item.url"
                class="flex items-center py-2 px-4 w-full rounded-lg hover:bg-white/20 transition-all"
                :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                <i v-if="item.icon" :class="[item.icon, 'mr-2']"></i>
                {{ item.label }}
              </a>
            </li>

            <li>
              <button @click="toggleConferenceDropdown"
                class="flex items-center justify-between py-2 px-4 w-full rounded-full hover:bg-white/20 transition-all"
                :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                <div class="flex items-center">
                  <i class="pi pi-bookmark mr-2"></i>
                  Past Conferences
                  <Badge v-if="recentConferences.length" class="ml-2" :value="recentConferences.length" severity="info" />
                </div>
                <i class="pi pi-angle-down"></i>
              </button>

              <div v-if="conferenceDropdownOpen" class="pl-4 mt-1">
                <div class="px-4 py-1 text-sm font-medium opacity-70">Recent Conferences</div>
                <template v-if="recentConferences.length">
                  <a v-for="conf in recentConferences" :key="conf.id" 
                     :href="`/conference/${conf.slug}`"
                     class="block py-2 px-4 rounded-lg hover:bg-white/20 transition-all"
                     :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                    {{ conf.title }} ({{ new Date(conf.startDate).getFullYear() }})
                  </a>
                </template>
                <div v-else class="px-4 py-2 text-sm italic opacity-60">
                  No recent conferences
                </div>
                
                <button @click="showAllConferences"
                  class="w-full text-left py-2 px-4 rounded-lg hover:bg-white/20 transition-all flex items-center my-1"
                  :class="{ 'text-white': !isScrolled, 'text-gray-800': isScrolled }">
                  <i class="pi pi-arrow-right mr-2"></i>
                  <span>Browse all past conferences</span>
                </button>
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