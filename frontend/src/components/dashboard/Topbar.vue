<template>
  <Card class="fixed-topbar shadow rounded-md" :pt="cardPt">
    <template #content>
      <div class="flex items-center justify-between w-full">
        <Button icon="pi pi-bars" text @click="toggleMobileMenu" class="md:hidden p-button-rounded"></Button>
        <div class="flex items-center gap-2 ml-auto">
          <Button @click="handleLogout" icon="pi pi-sign-out" text rounded></Button>
        </div>
      </div>
    </template>
  </Card>
</template>

<script>
import Button from 'primevue/button';
import Card from 'primevue/card';

export default {
  name: 'DashboardTopbar',
  components: {
    Button,
    Card
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
  methods: {
    toggleMobileMenu() {
      this.$emit('toggle-mobile-menu');
    },
    handleLogout() {
      this.$emit('logout');
    }
  }
};
</script>

<style scoped>

.fixed-topbar {
  position: fixed;
  top: 0;
  right: 0;
  left: 0;
  height: 64px; 
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
}

:deep(.p-card-content) {
  padding: 0 1rem;
  height: 100%;
}

@media (min-width: 769px) {
  .fixed-topbar {
    left: 280px; 
  }
}

@media (min-width: 769px) {
  .md\:hidden {
    display: none;
  }
}
</style>