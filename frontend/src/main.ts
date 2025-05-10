import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config';

import { primevueConfig } from './plugins/primevue';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';

import App from './App.vue'
import router from './router'

import { useAuthStore } from '@/stores/authStore';


import './plugins/axios';



startApp();

async function startApp () {
    const pinia = createPinia()
    const app = createApp(App)
    app.use(pinia)
    try {
        const authStore = useAuthStore();
        const auth = await authStore.checkAuth();
        console.log(auth);
    } catch (error) {
        console.error('Authentication check failed:', error);
    }
    app.use(ToastService);
    app.use(ConfirmationService);
    app.use(PrimeVue, primevueConfig);
    app.use(router)
    app.mount('#app');
}