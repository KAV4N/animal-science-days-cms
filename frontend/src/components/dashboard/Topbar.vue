<template>
  <Card class="fixed-topbar shadow rounded-md" :pt="cardPt">
    <template #content>
      <div class="flex items-center justify-between w-full">
        <Button icon="pi pi-bars" text @click="toggleMobileMenu" class="md:hidden p-button-rounded p-button-sm"></Button>
        <div class="flex items-center gap-2 ml-auto">
          <Button
            icon="pi pi-home"
            text
            rounded
            size="small"
            v-tooltip.bottom="{ value: 'Go to public home page', pt: { text: 'px-3 py-1 text-xs rounded-2xl bg-neutral-900 text-white shadow border-none' } }"
            @click="$router.push({ name: 'HomePage' })"
          />
        </div>
      </div>
    </template>
  </Card>
</template>

<script>
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tooltip from 'primevue/tooltip';
import { useAuthStore } from '@/stores/authStore';

export default {
  name: 'DashboardTopbar',
  components: {
    Button,
    Card
  },
  directives: {
    Tooltip
  },
  props: {
    sidebarVisible: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      cardPt: {
        body: { class: 'p-0' },
        content: { class: 'p-0 flex items-center h-full' },
        root: { class: 'border-none rounded-none' }
      }
    };
  },
  
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  methods: {
    toggleMobileMenu() {
      this.$emit('toggle-mobile-menu');
    },
  }
};
</script>

<style scoped>
.fixed-topbar {
  position: fixed;
  top: 0;
  right: 0;
  left: 0;
  height: 48px; /* Reduced from 64px */
  z-index: 9;
  transition: left 0.3s ease;
}

:deep(.p-card) {
  height: 100%;
  box-shadow: none;
}

:deep(.p-card-body) {
  padding: 0;
  height: 100%;
  border-color: rgba(82, 82, 82, 0.26);
  border-bottom-width: 1px !important;
}

:deep(.p-card-content) {
  padding: 0 0.75rem;
  height: 100%;
}

@media (min-width: 769px) {
  .fixed-topbar {
    left: 220px; /* Reduced from 280px to match sidebar width */
  }
}

@media (min-width: 769px) {
  .md\:hidden {
    display: none;
  }
}
</style>
