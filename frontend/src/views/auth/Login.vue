<template>
  <div>
    <LoginCard :visible="true" @login="onLoginSuccess" />
  </div>
</template>
  
<script lang="ts">
import { defineComponent } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import LoginCard from '@/components/auth/LoginCard.vue';
  
export default defineComponent({
  name: 'LoginView',
  components: {
    LoginCard
  },
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  methods: {
    onLoginSuccess() {
      
    }
  },

});
</script>
