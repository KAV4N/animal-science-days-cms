<template>
    <div>
      <ChangePasswordCard />
    </div>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import ChangePasswordCard from '@/components/auth/ChangePasswordCard.vue';
export default defineComponent({
  name: 'ChangePasswordView',
  components: {
    ChangePasswordCard
  },
  setup() {
    const authStore = useAuthStore();

    return {
      authStore
    };
  },
});
</script>
